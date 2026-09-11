<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\Concerns\SyncsOrganizationChanges;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    use HasDataTable;
    use SyncsOrganizationChanges;

    /**
     * Display a listing of the resource.
     */
    
    public function index(Request $request)
    {
        Log::channel('department_management')->info('Accessed the Department Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Department::class,
            [
                'searchableColumns' => ['name', 'description', 'code', 'status', 'updated_at'],
                'allowedSortColumns' => ['name', 'group.name', 'division.name', 'description', 'status', 'updated_at'],
                'selectColumns' => ['id', 'name', 'description', 'group_id', 'division_id', 'status', 'updated_at',],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'relationshipColumns' => ['group' => ['name'], 'division' => ['name']],
                'eagerLoad' => ['group', 'division'],
                'isShowColumns' => true
            ]
        );

        $results['data'] = collect($results['data'])->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'group_id' => $item->group_id,
                'group' => $item->group?->name,
                'division_id' => $item->division_id,
                'division' => $item->division?->name,
                'description' => $item->description,
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        return inertia('branch_and_office_management/departments/index', $results);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Group::all();
        return Inertia::render('branch_and_office_management/departments/create', [
            'groups' => $groups
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('departments', 'name')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'code' => ['required', Rule::unique('departments', 'code')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'description' => 'required',
            'group' => 'nullable',
            'division' => 'nullable',
        ]);

        $groupData = Group::select('id')->where('name', $request->group)->first();
        $divisionData = Division::select('id')->where('name', $request->division)->first();

        $data = [
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'description' => $validatedData['description'],
            'group_id' => $groupData->id ?? null,
            'division_id' => $divisionData->id ?? null,
            'status' => 'active',
        ];

        try {
            $department = Department::create($data);
            $newData = $department->getAttributes();

            $this->syncOrgChange('DEPARTMENT', 'create', $department->toSyncPayload());

            Log::channel('department_management')->info('Created a new Department', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);
            return redirect()->back()->with(['success' => 'Department created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('department_management')->error('Error creating new Department', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedData)]);
            return redirect()->back()->withErrors(['add_department' => 'Failed to create new department.' . $th]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $groups = Group::all();
        return Inertia::render('branch_and_office_management/departments/dialog/edit', [
            'department' => $department,
            'groups' => $groups
        ]);
    }

    /**

     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department_management)
    {
        $validStatus = ['active', 'inactive'];
        $validRemarks = ['Expired', 'Deactivated', 'On Hold', 'Rejected'];
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('departments', 'name')->whereNull('deleted_at')->ignore($department_management->id)],
            'code' => ['required', Rule::unique('departments', 'code')->whereNull('deleted_at')->ignore($department_management->id)],
            'description' => 'required',
            'group' => 'nullable',
            'division' => 'nullable',
            'status' => ['required', Rule::in($validStatus)],
            'remarks' => ['nullable', Rule::in($validRemarks)]
        ]);

        if ($validatedData['status'] === 'active') {
            $validatedData['remarks'] = '';
        }

        $groupData = Group::select('id')->where('name', $request->group)->first();
        $divisionData = Division::select('id')->where('name', $request->division)->first();

        $data = [
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'description' => $validatedData['description'],
            'group_id' => $groupData->id ?? null,
            'division_id' => $divisionData->id ?? null,
            'status' => $validatedData['status'],
            'remarks' => $validatedData['remarks'] ?? null,
        ];

        $oldData      = $department_management->getAttributes();
        $previousCode = $department_management->code;

        try {
            $department_management->update($data);
            $newData = $department_management->getAttributes();

            // Tell SSO the OLD code too when it just changed, so SSO's org-sync lookup
            // (which is keyed on the same mutable code column) can still find and update
            // this same row instead of creating a duplicate. See SsoController::orgSync().
            $syncPayload = $department_management->toSyncPayload();
            if ($previousCode !== $data['code']) {
                $syncPayload['previous_code'] = $previousCode;
            }

            $this->syncOrgChange('DEPARTMENT', 'update', $syncPayload);

            $response = [
                'message' => 'Department updated successfully',
                'department' => $department_management,
            ];

            Log::channel('department_management')->info('Updated a Department', [
                'user_id' => Auth::id(),
                'selected_department_id' => $department_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData)
            ]);

            return $this->respondWithSuccess($request, $response);
        } catch (\Throwable $th) {
            $errorResponse = [
                'message' => 'Failed to update department',
            ];

            Log::channel('department_management')->info('Error updating Department', [
                'user_id' => Auth::id(),
                'selected_department_id' => $department_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($validatedData)
            ]);

            return $this->respondWithError($request, $errorResponse, $th, 'edit_department');
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
    public function destroy(Department $department_management)
    {
        if ($department_management->headOfficeUser()->exists() || $department_management->section()->exists()) {
            return redirect()->back()->withErrors(['delete_department' => 'Cannot Delete Department. It is assigned to an existing user or office. To persist delete, change the status instead']);
        }

        $id      = $department_management->id;
        $payload = $department_management->toSyncPayload();

        try {
            $department_management->delete();

            $this->syncOrgChange('DEPARTMENT', 'delete', $payload);

            Log::channel('department_management')->info('Deleted a Department', ['user_id' => Auth::id(), 'deleted_department_id' => $id]);
            return redirect()->back()->with(['success' => 'Department deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('department_management')->error('Error deleting Department', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_department_id' => $id]);
            return redirect()->back()->withErrors(['delete_department' => 'Failed to delete department.' . $th]);
        }
    }
}
