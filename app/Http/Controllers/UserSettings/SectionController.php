<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\Concerns\SyncsOrganizationChanges;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Section;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SectionController extends Controller
{
    use HasDataTable;
    use SyncsOrganizationChanges;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('section_management')->info('Accessed the Section Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Section::class,
            [
                'searchableColumns' => ['name', 'description', 'code', 'status', 'updated_at'],
                'allowedSortColumns' => ['name', 'group.name', 'division.name', 'department.name', 'description', 'status', 'updated_at'],
                'selectColumns' => ['id', 'name', 'description', 'group_id', 'division_id', 'department_id', 'status', 'updated_at',],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'relationshipColumns' => ['group' => ['name'], 'division' => ['name'], 'department' => ['name']],
                'eagerLoad' => ['group', 'division', 'department',],
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
                'department_id' => $item->department_id,
                'department' => $item->department?->name,
                'description' => $item->description,
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        return inertia('branch_and_office_management/sections/index', $results);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Group::all();
        return Inertia::render('branch_and_office_management/sections/dialog/create', [

            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('sections', 'name')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'code' => ['required', Rule::unique('sections', 'code')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'description' => 'required',
            'group' => 'nullable',
            'division' => 'nullable',
            'department' => 'nullable'
        ]);

        $groupData = Group::select('id')->where('name', $request->group)->first();
        $divisionData = Division::select('id')->where('name', $request->division)->first();
        $departmentData = Department::select('id')->where('name', $request->department)->first();

        $data = [
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'description' => $validatedData['description'],
            'group_id' => $groupData->id ?? null,
            'division_id' => $divisionData->id ?? null,
            'department_id' => $departmentData->id ?? null,
            'status' => 'active',
        ];

        try {
            $section_management = Section::create($data);
            $newData = $section_management->getAttributes();

            $this->syncOrgChange('SECTION', 'create', $section_management->toSyncPayload());

            Log::channel('section_management')->info('Created a new Section', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);
            return redirect()->back()->with(['success' => 'Section created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('section_management')->error('Error creating new Section', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedData)]);
            return redirect()->back()->withErrors(['add_section' => 'Failed to create new section.' . $th]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section_management)
    {
        $divisions = Division::all();
        $departments = Department::all();
        return Inertia::render('branch_and_office_management/sections/dialog/edit', [
            'department' => $departments,
            'divisions' => $divisions,
            'section' => $section_management
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section_management)
    {
        $validStatus = ['active', 'inactive'];
        $validRemarks = ['Expired', 'Deactivated', 'On Hold', 'Rejected'];
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('sections', 'name')->whereNull('deleted_at')->ignore($section_management->id)],
            'code' => ['required', Rule::unique('sections', 'code')->whereNull('deleted_at')->ignore($section_management->id)],
            'description' => 'required',
            'group' => 'nullable',
            'division' => 'nullable',
            'department' => 'nullable',
            'status' => ['required', Rule::in($validStatus)],
            'remarks' => ['nullable', Rule::in($validRemarks)]
        ]);

        if ($validatedData['status'] === 'active') {
            $validatedData['remarks'] = '';
        }

        $groupData = Group::select('id')->where('name', $request->group)->first();
        $divisionData = Division::select('id')->where('name', $request->division)->first();
        $departmentData = Department::select('id')->where('name', $request->department)->first();

        $data = [
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'description' => $validatedData['description'],
            'group_id' => $groupData->id ?? null,
            'division_id' => $divisionData->id ?? null,
            'department_id' => $departmentData->id ?? null,
            'status' => $validatedData['status'],
            'remarks' => $validatedData['remarks'] ?? null,
        ];

        $oldData      = $section_management->getAttributes();
        $previousCode = $section_management->code;

        try {
            $section_management->update($data);
            $newData = $section_management->getAttributes();

            // Tell SSO the OLD code too when it just changed, so SSO's org-sync lookup
            // (which is keyed on the same mutable code column) can still find and update
            // this same row instead of creating a duplicate. See SsoController::orgSync().
            $syncPayload = $section_management->toSyncPayload();
            if ($previousCode !== $data['code']) {
                $syncPayload['previous_code'] = $previousCode;
            }

            $this->syncOrgChange('SECTION', 'update', $syncPayload);

            $response = [
                'message' => 'Section updated successfully',
                'section' => $section_management
            ];

            Log::channel('section_management')->info('Updated a Section', [
                'user_id' => Auth::id(),
                'selected_section_id' => $section_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData)
            ]);

            return $this->respondWithSuccess($request, $response);
        } catch (\Throwable $th) {
            $errorResponse = [
                'message' => 'Failed to update section',
            ];

            Log::channel('section_management')->info('Error updating Section', [
                'user_id' => Auth::id(),
                'selected_section_id' => $section_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($validatedData)
            ]);

            return $this->respondWithError($request, $errorResponse, $th, 'edit_section');
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
    public function destroy(Section $section_management)
    {

        if ($section_management->headOfficeUser()->exists()) {
            return redirect()->back()->withErrors(['delete_section' => 'Cannot Delete Section. It is assigned to an existing user or office. To persist delete, change the status instead']);
        }

        $id      = $section_management->id;
        $payload = $section_management->toSyncPayload();

        try {
            $section_management->delete();

            $this->syncOrgChange('SECTION', 'delete', $payload);

            Log::channel('section_management')->info('Deleted a Section', ['user_id' => Auth::id(), 'deleted_section_id' => $id]);
            return redirect()->back()->with(['success' => 'Section deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('section_management')->error('Error deleting Section', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_section_id' => $id]);
            return redirect()->back()->withErrors(['delete_section' => 'Failed to delete section.' . $th]);
        }
    }
}
