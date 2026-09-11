<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\Concerns\SyncsOrganizationChanges;
use App\Models\Branch;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{

    use HasDataTable;
    use SyncsOrganizationChanges;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('branch_management')->info('Accessed the Branch Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Branch::class,
            [
                'searchableColumns' => ['branch_code', 'name', 'location', 'status', 'updated_at'],
                'allowedSortColumns' => ['branch_code', 'name', 'location', 'status', 'updated_at'],
                'selectColumns' => ['id', 'branch_code', 'name', 'location', 'status', 'updated_at'],
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
                'branch_code' => $item->branch_code,
                'name' => $item->name,
                'location' => $item->location,
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        return inertia('branch_and_office_management/branches/index', $results);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('branches', 'name')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'branch_code' => ['required', Rule::unique('branches', 'branch_code')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'location' => 'required'
        ]);

        $validatedData['status'] = 'active';

        try {
            $branch = Branch::create($validatedData);
            $newData = $branch->getAttributes();

            $this->syncOrgChange('BRANCH', 'create', $branch->toSyncPayload());

            Log::channel('branch_management')->info('Created a new Branch', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);
            return redirect()->back()->with(['success' => 'Branch created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('branch_management')->error('Error creating new Branch', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedData)]);
            return redirect()->back()->withErrors(['add_branch' => 'Failed to create new branch.' . $th]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch_management)
    {
        $validStatus = ['active', 'inactive'];
        $validRemarks = ['Expired', 'Deactivated', 'On Hold', 'Rejected'];

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches', 'name')->whereNull('deleted_at')->ignore($branch_management->id)],
            'branch_code' => ['required', 'string', 'max:255', Rule::unique('branches', 'branch_code')->whereNull('deleted_at')->ignore($branch_management->id)],
            'location' => ['required', 'string'],
            'status' => ['required', Rule::in($validStatus)],
            'remarks' => ['nullable', Rule::in($validRemarks)]
        ], [
            'name.required' => 'Branch name is required.',
            'name.unique' => 'Branch name already exists.',
            'branch_code.required' => 'Branch Code is required.',
            'branch_code.unique' => 'Branch Code already exists.',
            'location.required' => 'Branch Location is required',
            'status.required' => 'Branch status is required.'
        ]);

        if ($validatedData['status'] === 'active') {
            $validatedData['remarks'] = '';
        }

        $oldData      = $branch_management->getAttributes();
        $previousCode = $branch_management->branch_code;

        try {
            $branch_management->update($validatedData);
            $newData = $branch_management->getAttributes();

            // Tell SSO the OLD code too when it just changed, so SSO's org-sync lookup
            // (which is keyed on the same mutable code column) can still find and update
            // this same row instead of creating a duplicate. See SsoController::orgSync().
            $syncPayload = $branch_management->toSyncPayload();
            if ($previousCode !== $validatedData['branch_code']) {
                $syncPayload['previous_code'] = $previousCode;
            }

            $this->syncOrgChange('BRANCH', 'update', $syncPayload);

            $response = [
                'message' => 'Branch updated successfully.',
                'branch' => $branch_management,
            ];

            Log::channel('branch_management')->info('Updated a Branch', [
                'user_id' => Auth::id(),
                'selected_branch_id' => $branch_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData)
            ]);

            return $this->respondWithSuccess($request, $response);
        } catch (\Throwable $th) {
            $errorResponse = [
                'message' => 'Failed to update branch.',
            ];

            Log::channel('branch_management')->error('Error updating Branch', [
                'user_id' => Auth::id(),
                'selected_branch_id' => $branch_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($validatedData)
            ]);

            return $this->respondWithError($request, $errorResponse, $th);
        }
    }

    private function respondWithSuccess(Request $request, array $response)
    {
        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()->back()->with(['success' => $response['message']]);
    }

    private function respondWithError(Request $request, array $errorResponse, \Throwable $th = null)
    {
        if ($request->expectsJson()) {
            return response()->json($errorResponse, 500);
        }

        $message = $errorResponse['message'];
        if ($th) {
            $message .= $th;
        }

        return redirect()->back()->withErrors(['edit_branch' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch_management)
    {
        if ($branch_management->users()->exists()) {
            return redirect()->back()->withErrors(['delete_branch' => 'Cannot Delete Branch. It is assigned to an existing user. To persist delete, change the status instead']);
        }

        $id      = $branch_management->id;
        $payload = $branch_management->toSyncPayload();

        try {
            $branch_management->delete();

            $this->syncOrgChange('BRANCH', 'delete', $payload);

            Log::channel('branch_management')->info('Deleted a Branch', ['user_id' => Auth::id(), 'deleted_branch_id' => $id]);
            return redirect()->back()->with(['success' => 'Branch deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('branch_management')->error('Error deleting Branch', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_branch_id' => $id]);
            return redirect()->back()->withErrors(['delete_branch' => 'Failed to delete branch.' . $th]);
        }
    }
}
