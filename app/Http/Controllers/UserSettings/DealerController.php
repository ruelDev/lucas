<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\Concerns\SyncsOrganizationChanges;
use App\Models\Dealer;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DealerController extends Controller
{

    use HasDataTable;
    use SyncsOrganizationChanges;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('dealer_management')->info('Accessed the Branch Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Dealer::class,
            [
                'searchableColumns' => ['dealer_code', 'name', 'location', 'status', 'updated_at'],
                'allowedSortColumns' => ['dealer_code', 'name', 'location', 'status', 'updated_at'],
                'selectColumns' => ['id', 'dealer_code', 'name', 'location', 'status', 'updated_at'],
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
                'dealer_code' => $item->dealer_code,
                'name' => $item->name,
                'location' => $item->location,
                'bank_account_name' => $item->bank_account_name,
                'bank_account' => $item->bank_account,
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        return inertia('branch_and_office_management/dealers/index', $results);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('dealers', 'name')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'dealer_code' => ['required', Rule::unique('dealers', 'dealer_code')->where(function ($query) {
                $query->whereNull('deleted_at');
            })],
            'location' => 'required',
            'bank_account_name' => 'nullable',
            'bank_account' => 'nullable',
        ]);

        $validatedData['status'] = 'active';

        try {
            $dealer = Dealer::create($validatedData);
            $newData = $dealer->getAttributes();

            $this->syncOrgChange('DEALER', 'create', $dealer->toSyncPayload());

            Log::channel('dealer_management')->info('Created a new Dealer', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);
            return redirect()->back()->with(['success' => 'Dealer created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('dealer_management')->error('Error creating new Dealer', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedData)]);
            return redirect()->back()->withErrors(['add_dealer' => 'Failed to create new dealer.' . $th]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dealer $dealer_management)
    {
        $validStatus = ['active', 'inactive'];

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('dealers', 'name')->whereNull('deleted_at')->ignore($dealer_management->id)],
            'dealer_code' => ['required', 'string', 'max:255', Rule::unique('dealers', 'dealer_code')->whereNull('deleted_at')->ignore($dealer_management->id)],
            'location' => 'required|string',
            'bank_account_name' => 'nullable',
            'bank_account' => 'nullable',
            'status' => ['required', Rule::in($validStatus)],
        ], [
            'name.required' => 'Dealer name is required.',
            'name.unique' => 'Dealer name already exists.',
            'dealer_code.required' => 'Dealer Code is required.',
            'dealer_code.unique' => 'Dealer Code already exists.',
            'location.required' => 'Dealer Location is required',
            'status.required' => 'Dealer status is required.'
        ]);

        $oldData      = $dealer_management->getAttributes();
        $previousCode = $dealer_management->dealer_code;

        try {
            $dealer_management->update($validatedData);
            $newData = $dealer_management->getAttributes();

            // Tell SSO the OLD code too when it just changed, so SSO's org-sync lookup
            // (which is keyed on the same mutable code column) can still find and update
            // this same row instead of creating a duplicate. See SsoController::orgSync().
            $syncPayload = $dealer_management->toSyncPayload();
            if ($previousCode !== $validatedData['dealer_code']) {
                $syncPayload['previous_code'] = $previousCode;
            }

            $this->syncOrgChange('DEALER', 'update', $syncPayload);

            $response = [
                'message' => 'Dealer updated successfully.',
                'dealer' => $dealer_management,
            ];

            Log::channel('dealer_management')->info('Updated a Dealer', [
                'user_id' => Auth::id(),
                'selected_dealer_id' => $dealer_management->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData)
            ]);

            return $this->respondWithSuccess($request, $response);
        } catch (\Throwable $th) {
            $errorResponse = [
                'message' => 'Failed to update dealer.' . $th,
            ];

            Log::channel('dealer_management')->error('Error updating Dealer', [
                'user_id' => Auth::id(),
                'selected_dealer_id' => $dealer_management->id,
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

    private function respondWithError(Request $request, array $errorResponse, \Throwable $th)
    {
        if ($request->expectsJson()) {
            return response()->json($errorResponse, 500);
        }

        $message = $errorResponse['message'];
        if ($th) {
            $message .= $th;
        }

        return redirect()->back()->withErrors(['edit_dealer' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dealer $dealer_management)
    {
        if ($dealer_management->users()->exists()) {
            return redirect()->back()->withErrors(['delete_dealer' => 'Cannot Delete Dealer. It is assigned to an existing user. To persist delete, change the status instead']);
        }

        $id      = $dealer_management->id;
        $payload = $dealer_management->toSyncPayload();

        try {
            $dealer_management->delete();

            $this->syncOrgChange('DEALER', 'delete', $payload);

            Log::channel('dealer_management')->info('Deleted a Dealer', ['user_id' => Auth::id(), 'deleted_dealer_id' => $id]);
            return redirect()->back()->with(['success' => 'Dealer deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('dealer_management')->error('Error deleting Dealer', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_dealer_id' => $id]);
            return redirect()->back()->withErrors(['delete_dealer' => 'Failed to delete dealer.' . $th]);
        }
    }
}
