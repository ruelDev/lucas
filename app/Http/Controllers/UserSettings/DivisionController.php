<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\Concerns\SyncsOrganizationChanges;
use App\Models\Division;
use App\Models\Group;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DivisionController extends Controller
{
    use HasDataTable;
    use SyncsOrganizationChanges;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('division_management')->info('Accessed the Division Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Division::class,
            [
                'searchableColumns' => ['name', 'description', 'code', 'status', 'updated_at'],
                'allowedSortColumns' => ['name', 'group.name', 'description', 'status', 'updated_at'],
                'selectColumns' => ['id', 'name', 'description', 'group_id', 'status', 'updated_at',],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'relationshipColumns' => ['group' => ['name']],
                'eagerLoad' => ['group'],
                'isShowColumns' => true
            ]
        );

        $results['data'] = collect($results['data'])->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'group_id' => $item->group_id,
                'group' => $item->group?->name ?? 'N/A',
                'description' => $item->description,
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();
        return inertia('branch_and_office_management/divisions/index', $results);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Group::all();
        return Inertia::render('branch_and_office_management/divisions/dialog/create', [
            'groups' => $groups
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('divisions', 'name')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'code' => ['required', Rule::unique('divisions', 'code')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'description' => 'required',
            'group' => 'nullable'
        ]);

        $groupData = Group::select('id')->where('name', $request->group)->first();

        $data = [
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'description' => $validatedData['description'],
            'group_id' => $groupData->id ?? null,
            'status' => 'active',
        ];

        try {
            $division = Division::create($data);
            $newData = $division->getAttributes();

            $this->syncOrgChange('DIVISION', 'create', $division->toSyncPayload());

            Log::channel('division_management')->info('Created a new Division', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);
            return redirect()->back()->with(['success' => 'Division created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('division_management')->error('Error creating new Division', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedData)]);
            return redirect()->back()->withErrors(['add_division' => 'Failed to create new division.' . $th]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Division $division_management)
    {
        $validStatus = ['active', 'inactive'];
        $validRemarks = ['Expired', 'Deactivated', 'On Hold', 'Rejected'];
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('divisions', 'name')->whereNull('deleted_at')->ignore($division_management->id)],
            'code' => ['required', Rule::unique('divisions', 'code')->whereNull('deleted_at')->ignore($division_management->id)],
            'description' => 'required',
            'group' => 'nullable',
            'status' => ['required', Rule::in($validStatus)],
            'remarks' => ['nullable', Rule::in($validRemarks)]
        ]);

        if ($validatedData['status'] === 'active') {
            $validatedData['remarks'] = '';
        }

        $groupData = Group::select('id')->where('name', $request->group)->first();

        $data = [
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'description' => $validatedData['description'],
            'group_id' => $groupData->id ?? null,
            'status' => $validatedData['status'],
            'remarks' => $validatedData['remarks'] ?? null,
        ];

        $oldData      = $division_management->getAttributes();
        $previousCode = $division_management->code;

        try {
            $division_management->update($data);
            $newData = $division_management->getAttributes();

            // Tell SSO the OLD code too when it just changed, so SSO's org-sync lookup
            // (which is keyed on the same mutable code column) can still find and update
            // this same row instead of creating a duplicate. See SsoController::orgSync().
            $syncPayload = $division_management->toSyncPayload();
            if ($previousCode !== $data['code']) {
                $syncPayload['previous_code'] = $previousCode;
            }

            $this->syncOrgChange('DIVISION', 'update', $syncPayload);

            $response = [
                'message' => 'Division updated successfully',
                'division' => $division_management,
            ];

            Log::channel('division_management')->info('Updated a Division', [
                'user_id' => Auth::id(),
                'selected_division_id' => $division_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData)
            ]);

            return $this->respondWithSuccess($request, $response);
        } catch (\Throwable $th) {
            $errorResponse = [
                'message' => 'Failed to update division',
            ];

            Log::channel('division_management')->info('Error updating Division', [
                'user_id' => Auth::id(),
                'selected_division_id' => $division_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($validatedData)
            ]);

            return $this->respondWithError($request, $errorResponse, $th, 'edit_division');
        }
    }

    private function respondWithSuccess(Request $request, array $response)
    {
        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()->back()->with(['success' => $response['message']]);
    }

    private function respondWithError(Request $request, array $errorResponse, \Throwable $th = null, string $errorKey = 'error')
    {
        if ($request->expectsJson()) {
            return response()->json($errorResponse, 500);
        }

        $message = $errorResponse['message'];
        if ($th) {
            $message .= $th;
        }

        return redirect()->back()->withErrors([$errorKey => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division_management)
    {

        if ($division_management->headOfficeUser()->exists() || $division_management->department()->exists() || $division_management->section()->exists()) {
            return redirect()->back()->withErrors(['delete_division' => 'Cannot Delete Division. It is assigned to an existing user or office. To persist delete, change the status instead']);
        }
        $id      = $division_management->id;
        $payload = $division_management->toSyncPayload();

        try {
            $division_management->delete();

            $this->syncOrgChange('DIVISION', 'delete', $payload);

            Log::channel('division_management')->info('Deleted a Division', ['user_id' => Auth::id(), 'deleted_division_id' => $id]);
            return redirect()->back()->with(['success' => 'Division deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('division_management')->error('Error deleting Division', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_division_id' => $id]);
            return redirect()->back()->withErrors(['delete_division' => 'Failed to delete division.' . $th]);
        }
    }
}
