<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Exports\UserExport;
use App\Mail\EmployeeMail;
use App\Models\User;
use App\Models\Role;
use App\Jobs\PushUserDeleteToSsoJob;
use App\Jobs\PushUserSyncToSsoJob;
use App\Models\UserHasBranchDealer;
use App\Models\UserHasHeadOffice;
use App\Services\UserSettings\UserManagementExportService;
use App\Services\UserSettings\UserManagementService;
use App\Services\UserSettings\UserManagementSyncPreparationService;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    use HasDataTable;
    use SoftDeletes;

    protected $defaultRegexStructure = 'regex:/^[a-zA-Z\s\-]+$/';

    protected $userManagementService;
    protected $userManagementSyncPreparationService;
    protected $userManagementExportService;

    protected $renderRoutes = [
        'index' => "user-management/index",
        'create' => "user-management/dialog/create",
        'edit' => "user-management/dialog/edit",
    ];

    public function __construct(
        UserManagementService $userManagementService,
        UserManagementSyncPreparationService $userManagementSyncPreparationService,
        UserManagementExportService $userManagementExportService
    ) {
        $this->userManagementService = $userManagementService;
        $this->userManagementSyncPreparationService = $userManagementSyncPreparationService;
        $this->userManagementExportService = $userManagementExportService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('user_management')->info('Accessed the User Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            User::class,
            [
                'searchableColumns' => [['fname', 'mname', 'lname'], 'employee_id', 'position', 'email', 'company', 'updated_at'],
                'allowedSortColumns' => ['employee_id', 'fname', 'position', 'status', 'updated_at'],
                'selectColumns' => ['id', 'fname', 'mname', 'lname', 'employee_id', 'email', 'company', 'position', 'isBranchDealer', 'status', 'remarks', 'updated_at'],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'eagerLoad' => [
                    'roles',
                    'branchDealerUser.branch',
                    'branchDealerUser.dealer',
                    'headOfficeUser.group',
                    'headOfficeUser.division',
                    'headOfficeUser.department',
                    'headOfficeUser.section',
                ],
                'isShowColumns' => true
            ]
        );

        $results['data'] = collect($results['data'])->map(function ($item) {
            $formatDate = fn($date) => $date?->format('Y-m-d H:i:s');

            $name = $item->fname . " " . ($item->mname ?? '') . " " . $item->lname;

            $area = $this->userManagementService->prepareHeadOfficeUserOrBranchDealerUser($item);

            $role = $item->roles->first()?->name;

            $session = $this->userManagementExportService->getLastSession($item->id);

            [$branchId, $dealerId, $type] = $this->userManagementService->prepareBranchDealerType($item);

            return [
                'roles' => Role::pluck("name")->toArray(),
                'id' => $item->id,
                'employee_id' => $item->employee_id,
                'name' => $name,
                'fname' => $item->fname,
                'mname' => $item->mname,
                'lname' => $item->lname,
                'email' => $item->email,
                'isBranchDealer' => $item->isBranchDealer,
                'type' => $type,
                'branch_id' => $branchId,
                'dealer_id' => $dealerId,
                'location' => $item->headOfficeUser?->location ?? null,
                'group_id' => $item->headOfficeUser?->group_id ?? null,
                'division_id' => $item->headOfficeUser?->division_id ?? null,
                'department_id' => $item->headOfficeUser?->department_id ?? null,
                'section_id' => $item->headOfficeUser?->section_id ?? null,
                'area' => $area,
                'company' => $item->company,
                'position' => $item->position,
                'role' => $role,
                'status' => $item->status,
                'remarks' => $item->remarks,
                'last_login_date' => $formatDate($session['last_login']),
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        return inertia($this->renderRoutes['index'], $results);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::select('id', 'name')
            ->whereNotIn('name', ['Admin', 'SuperAdmin'])
            ->get();
        return Inertia::render($this->renderRoutes['create'], [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        [$validatedUserData, $validatedBranchDealerData, $validatedOfficeData] = $this->userManagementService->validateNewUserData($request);

        DB::beginTransaction();
        try {
            $user = User::create($validatedUserData);
            $user->syncRoles($validatedUserData['role']);
            $isBranchDealer = $user->isBranchDealer;

            $this->userManagementService->syncNewUserBranchDealerOrOffice($user, $isBranchDealer, $validatedBranchDealerData, $validatedOfficeData);

            $new_data = $user->getAttributes();

            DB::commit();

            PushUserSyncToSsoJob::syncOrQueue($user->fresh());

            // send mail notification via user email
            Mail::to($validatedUserData['email'])->send(new EmployeeMail($validatedUserData));

            Log::channel('user_management')->info('Created a new User', ['user_id' => Auth::id(), 'new_data' => json_encode($new_data)]);

            return to_route("user-management.index")->with(['success' => 'User created successfully.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('user_management')->error('Error creating new User', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedUserData)]);
            return redirect()->back()->withErrors(['add_user' => 'Failed to create new user.' . $th]);
        }
    }

    public function view(Request $request)
    {
        // Validate that ID is provided
        $request->validate([
            'id' => 'required|integer|exists:users,id'
        ], [
            'id.required' => 'User ID is required to view the edit form.',
            'id.exists' => 'The selected user does not exist.'
        ]);

        $id = $request->input('id');

        $user_management = User::with([
            'roles',
            'branchDealerUser.branch',
            'branchDealerUser.dealer',
            'headOfficeUser.group',
            'headOfficeUser.division',
            'headOfficeUser.department',
            'headOfficeUser.section',
        ])->find($id);

        if ($user_management->isBranchDealer == '2') {
            $user_management->location = $user_management->headOfficeUser->location;
            $user_management->organization = $this->userManagementSyncPreparationService->prepareHeadOfficeOrganizationData($user_management->headOfficeUser);
        } else {
            $user_management->branchDealerId = $this->userManagementSyncPreparationService->prepareBranchDealerData($user_management->branchDealerUser);
        }

        if (!$user_management) {
            return redirect()->route("user-management.index")
                ->with(['error' => 'User not found']);
        }

        $roles = Role::select('id', 'name')
            ->whereNotIn('name', ['Admin', 'SuperAdmin'])
            ->get();

        return Inertia::render($this->renderRoutes['edit'], [
            "user" => $user_management,
            'roles' => $roles,
        ]);
    }

    public function show()
    {
        return redirect()->route("user-management.index")
            ->with(['error' => 'User not found']);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user_management)
    {
        $originalData = $user_management->getAttributes();

        DB::beginTransaction();
        try {
            [$validatedUserData, $validatedBranchData, $validatedOfficeData] = $this->userManagementService->validateExistingUserData($request, $user_management);

            $user_management->update($validatedUserData);
            $user_management->syncRoles($validatedUserData['role']);

            $oldIsBranch = (int) $originalData['isBranchDealer'];
            $isBranchDealer = (int) $validatedUserData['isBranchDealer'];

            $this->userManagementService->syncExistingUserBranchOrOffice($user_management, $isBranchDealer, $oldIsBranch, $validatedBranchData, $validatedOfficeData);

            DB::commit();

            PushUserSyncToSsoJob::syncOrQueue($user_management->fresh());

            $newData = $user_management->getAttributes();

            Log::channel('user_management')->info('Updated a User', [
                'user_id' => Auth::id(),
                'selected_user_id' => $user_management->id,
                'old_data' => json_encode($originalData),
                'new_data' => json_encode($newData)
            ]);

            return to_route("user-management.index")->with([
                'success' => 'User updated successfully.'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::channel('user_management')->warning('Validation error updating User', [
                'user_id' => Auth::id(),
                'selected_user_id' => $user_management->id,
                'errors' => $e->errors()
            ]);

            // Re-render the edit view with validation errors
            $user_management->refresh();
            $user_management->load([
                'roles',
                'branchDealerUser.branch',
                'branchDealerUser.dealer',
                'headOfficeUser.group',
                'headOfficeUser.division',
                'headOfficeUser.department',
                'headOfficeUser.section',
            ]);

            $roles = Role::select("id", "name")->get();

            return Inertia::render($this->renderRoutes['edit'], [
                "user" => $user_management,
                'roles' => $roles,
                'errors' => $e->errors(),
                'old' => $request->all(),
            ])->withViewData(['errors' => $e->errors()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('user_management')->error('Error updating User', [
                'user_id' => Auth::id(),
                'Reason' => $th->getMessage(),
                'selected_user_id' => $user_management->id,
                'old_data' => json_encode($originalData)
            ]);

            // Re-render the edit view with error message
            $user_management->refresh();
            $user_management->load([
                'roles',
                'branchDealerUser.branch',
                'branchDealerUser.dealer',
                'headOfficeUser.group',
                'headOfficeUser.division',
                'headOfficeUser.department',
                'headOfficeUser.section',
            ]);

            $roles = Role::select("id", "name")->get();

            return Inertia::render($this->renderRoutes['edit'], [
                "user" => $user_management,
                'roles' => $roles,
                'errors' => ['edit_user' => 'Failed to update user: ' . $th->getMessage()],
                'old' => $request->all(),
            ])->withViewData(['errors' => ['edit_user' => 'Failed to update user: ' . $th->getMessage()]]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user_management)
    {
        $auditLogs = $user_management->auditLogs()->where('user_id', $user_management->id)->get();

        foreach ($auditLogs as $log) {
            $oldData = json_decode($log->old_data, true);
            $newData = json_decode($log->new_data, true);

            $targetUserId = $oldData['id'] ?? $newData['id'] ?? null;

            if (!in_array($log->module, ['Session', 'Authentication']) && ($log->module !== 'User Management' || $targetUserId != $user_management->id)) {
                return redirect()->back()->withErrors(['delete_user' => 'Cannot Delete User. It is assigned or referenced to an existing transactions']);
            }
        }

        $userId     = $user_management->id;
        $employeeId = $user_management->employee_id;

        DB::beginTransaction();
        try {
            if ($user_management->isBranchDealer == '1') {
                UserHasBranchDealer::where('user_id', $userId)->delete();
            } elseif ($user_management->isBranchDealer == '2') {
                UserHasHeadOffice::where('user_id', $userId)->delete();
            }

            $user_management->delete();

            DB::commit();

            PushUserDeleteToSsoJob::deleteOrQueue($employeeId);

            Log::channel('user_management')->info('Deleted a User', ['user_id' => Auth::id(), 'deleted_user_id' => $userId]);
            return to_route("user-management.index")->with(['success' => 'User deleted successfully.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('user_management')->error('Error deleting User', ['user_id' => Auth::id(), 'Reason' => $th, 'selected_user_id' => $userId]);
            return redirect()->back()->withErrors(['delete_user' => 'Failed to delete User.']);
        }
    }

    public function resetPassword(User $user_management)
    {
        $new['fname'] = $user_management->fname;
        $new['title'] = 'Recovery Password Request';
        $new['employee_id'] = $user_management->employee_id;
        $new['raw_password'] = "BMI-" . Str::password(6, true, true, false ,false);
        $new['message'] = 'Recovery Password Request';

        try {
            // send mail notification via user email
            Mail::to($user_management->email)->send(new EmployeeMail($new));

            $new['password'] = Hash::make($new['raw_password']);

            $user_management->update([
                'password' => $new['password'],
                'isReset' => 0,
                'status' => 'active',
            ]);

            Log::info('Reset User Password', ['user_id' => Auth::id(), 'new_data' => json_encode($user_management->getAttributes())]);

            // return to_route("user-management.index")->with(['success' => 'User reset successfully.']);
            return to_route("user-management.index")->with(['success' => 'Password reset email sent. The email includes a reminder about the LUCAS vs. SSO password distinction.']);
        } catch (\Throwable $th) {
            Log::channel('user_management')->error('Error resetting User Password', ['user_id' => Auth::id(), 'Reason' => $th, 'selected_user_id' => $user_management->id]);
            return redirect()->back()->with(['success' => 'Failed to reset User Password.' . $th]);
        }
    }

    public function export(Request $request)
    {
        $searchVal = $request->input('searchVal') ?? '';
        $sortBy = $request->input('sortBy') ?? 'employee_id';
        $sortDir = $request->input('sortDir') ? 'DESC' : 'ASC';
        $exportType = $request->input('exportType');

        $users = json_decode(json_encode($this->userManagementExportService->umeQuery($searchVal, $sortBy, $sortDir)));

        Log::channel('user_management')->info('Exported Users list', ['user_id' => Auth::id()]);

        if ($exportType === 'pdf') {
            $meta = [];

            if (!empty($searchVal)) {
                $meta[] = "Search Key Used: {$searchVal}";
            }

            if (!empty($sortBy)) {
                $meta[] = "Sort By: " . ucfirst($sortBy) . " - {$sortDir}";
            }

            $metaText = implode(' ', $meta);

            $pdf = Pdf::loadView('exports.users_export', [
                'users' => $users,
                'searchVal' => $searchVal,
                'metaText' => $metaText,
            ])
                ->setPaper('letter', 'landscape')
                ->setOptions(['defaultFont' => 'helvetica']);

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="users.pdf');
        } elseif ($exportType === 'csv' || $exportType === 'xlsx') {
            return Excel::download(new UserExport($users), 'users.' . $exportType);
        }
    }

    public function getUserOptions()
    {
        $users = User::get();

        return response()->json($users->map(fn($item) => [
            'employee_id' => $item->employee_id,
            'name' => "{$item->fname} {$item->mname} {$item->lname}"
        ]));
    }
}
