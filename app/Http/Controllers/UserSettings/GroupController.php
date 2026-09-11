<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\Concerns\SyncsOrganizationChanges;
use App\Models\Group;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    use HasDataTable;
    use SyncsOrganizationChanges;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('group_management')->info('Accessed the Group Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Group::class,
            [
                'searchableColumns' => ['name', 'description', 'code', 'status', 'updated_at'],
                'allowedSortColumns' => ['name', 'description', 'status', 'updated_at'],
                'selectColumns' => ['id', 'name', 'description', 'status', 'updated_at'],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'relationshipColumns' => [],
                'eagerLoad' => [],
                'isShowColumns' => true
            ]
        );

        $results['data'] = collect($results['data'])->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'description' => $item->description,
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        return inertia('branch_and_office_management/groups/index', $results);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('groups', 'name')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'code' => ['required', Rule::unique('groups', 'code')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'description' => 'required'
        ]);

        $validatedData['status'] = 'active';

        try {
            $group = Group::create($validatedData);
            $newData = $group->getAttributes();

            $this->syncOrgChange('GROUP', 'create', $group->toSyncPayload());

            Log::channel('group_management')->info('Created a new Group', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);
            return redirect()->back()->with(['success' => 'Group created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('group_management')->error('Error creating new Group', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedData)]);
            return redirect()->back()->withErrors(['add_group' => 'Failed to create new group.' . $th]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group_management)
    {
        $validStatus = ['active', 'inactive'];
        $validRemarks = ['Expired', 'Deactivated', 'On Hold', 'Rejected'];
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('groups', 'name')->whereNull('deleted_at')->ignore($group_management->id)],
            'code' => ['required', Rule::unique('groups', 'code')->whereNull('deleted_at')->ignore($group_management->id)],
            'description' => 'required',
            'status' => ['required', Rule::in($validStatus)],
            'remarks' => ['nullable', Rule::in($validRemarks)]
        ]);

        if ($validatedData['status'] === 'active') {
            $validatedData['remarks'] = '';
        }

        $oldData      = $group_management->getAttributes();
        $previousCode = $group_management->code;

        try {
            $group_management->update($validatedData);
            $newData = $group_management->getAttributes();

            // Tell SSO the OLD code too when it just changed, so SSO's org-sync lookup
            // (which is keyed on the same mutable code column) can still find and update
            // this same row instead of creating a duplicate. See SsoController::orgSync().
            $syncPayload = $group_management->toSyncPayload();
            if ($previousCode !== $validatedData['code']) {
                $syncPayload['previous_code'] = $previousCode;
            }

            $this->syncOrgChange('GROUP', 'update', $syncPayload);

            $response = [
                'message' => 'Group update successfully',
                'group' => $group_management,
            ];

            Log::channel('group_management')->info('Updated a Group', [
                'user_id' => Auth::id(),
                'selected_group_id' => $group_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData)
            ]);

            return $this->respondWithSuccess($request, $response);
        } catch (\Throwable $th) {
            $errorResponse = [
                'message' => 'Failed to update group',
            ];

            Log::channel('group_management')->error('Error updating Group', [
                'user_id' => Auth::id(),
                'Reason' => $th,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($validatedData)
            ]);

            return $this->respondWithError($request, $errorResponse, $th, 'edit_group');
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
    public function destroy(Group $group_management)
    {
        if ($group_management->headOfficeUser()->exists() || $group_management->division()->exists() || $group_management->department()->exists() || $group_management->section()->exists()) {
            return redirect()->back()->withErrors(['delete_group' => 'Cannot Delete Group. It is assigned to an existing user or office. To persist delete, change the status instead']);
        }

        $id      = $group_management->id;
        $payload = $group_management->toSyncPayload();
        try {
            $group_management->delete();

            $this->syncOrgChange('GROUP', 'delete', $payload);

            Log::channel('group_management')->info('Deleted a Group', ['user_id' => Auth::id(), 'deleted_group_id' => $id]);
            return redirect()->back()->with(['success' => 'Group deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('group_management')->error('Error deleting Group', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_group_id' => $id]);
            return redirect()->back()->withErrors(['delete_group' => 'Failed to delete group.' . $th]);
        }
    }
}
