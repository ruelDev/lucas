<?php

namespace App\Http\Controllers\MasterSetup\BankManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterSetup\BankManagement\BankAccountManagementRequest;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Traits\HasAuditLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BankAccountManagementController extends Controller
{
    use HasAuditLog;

    private const ERROR_MESSAGE = 'Something went wrong. Please try again.';
    protected $module = 'Bank Account Management';

    public function index(Request $request, Bank $bank)
    {
        $search = $request->input('search', '');
        $page = (int) $request->input('page', 1);
        $sortDirection = $request->input('direction', 'desc');
        $sortBy = $request->input('sort', 'created_at');
        $per_page = (int) $request->input('per_page', 10);

        $accounts = BankAccount::with('banks')
            ->where('bank_id', $bank->id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('account_number', 'LIKE', "%{$search}%")
                        ->orWhere('depository_remarks', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy($sortBy, $sortDirection)
            ->get();

        $sorted = $sortDirection === 'asc'
            ? $accounts->sortBy($sortBy)->values()
            : $accounts->sortByDesc($sortBy)->values();

        $total = $sorted->count();
        $accounts = $sorted->slice(($page - 1) * $per_page, $per_page)->values();

        $paginator = new LengthAwarePaginator($accounts, $total, $per_page, $page);

        $items = [
            'bank'              => $bank,
            'bank_accounts'     => $accounts->map(fn($account) => [
                'id'                 => $account->id,
                'bank_id'            => $account->bank_id,
                'account_number'     => $account->account_number,
                'depository_remarks' => $account->depository_remarks,
                'created_at'         => $account->created_at
            ]),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'filters' => ['search' => $search],
            'sort'    => ['column' => $sortBy, 'direction' => $sortDirection]
        ];

        return inertia('master-setup/bank-management/bank-accounts/index', $items);
    }

    public function create(Bank $bank)
    {
        return inertia('master-setup/bank-management/bank-accounts/actions/create', ['bank' => $bank]);
    }

    public function store(Bank $bank, BankAccountManagementRequest $bankAccountManagementRequest)
    {
        try {
            $bankAccount = BankAccount::create($bankAccountManagementRequest->validated());

            $this->auditLogs('CREATED', $this->module, $bankAccount);

            return redirect("/bank-management/$bank->id/bank-accounts")->with('success', 'Bank account added successfully.');
        } catch (QueryException $e) {
            return redirect("/bank-management/$bank->id/bank-accounts")->with('error', 'Failed to save bank account. Please try again.');
        } catch (\Exception $e) {
            return redirect("/bank-management/$bank->id/bank-accounts")->with('error', self::ERROR_MESSAGE . $e->getMessage());
        }
    }

    public function edit(Bank $bank, BankAccount $bankAccount)
    {
        return inertia('master-setup/bank-management/bank-accounts/actions/edit', [
            'bank' => $bank,
            'bankAccount' => $bankAccount
        ]);
    }

    public function update(Bank $bank, BankAccountManagementRequest $bankAccountManagementRequest)
    {
        try {
            $validated = $bankAccountManagementRequest->validated();

            $bankAccount = BankAccount::find($validated['id']);

            $bankAccount->update($validated);

            $this->auditLogs('UPDATED', $this->module, $bankAccount);

            return redirect("/bank-management/$bank->id/bank-accounts")->with('success', 'Bank account updated successfully.');
        } catch (QueryException $e) {
            return redirect("/bank-management/$bank->id/bank-accounts")->with('error', 'Failed to update bank account. Please try again.');
        } catch (\Exception $e) {
            return redirect("/bank-management/$bank->id/bank-accounts")->with('error', self::ERROR_MESSAGE . $e->getMessage());
        }
    }

    public function destroy(Bank $bank, BankAccount $bankAccount)
    {
        try {
            $bankAccount->delete();

            $this->auditLogs('DELETED', $this->module, $bankAccount);

            return back()->with('success', 'Bank account deleted successfully.');
        } catch (QueryException $e) {
            return back()->with('error', 'Failed to delete bank account. Please try again.');
        } catch (\Exception $e) {
            return back()->with('error', self::ERROR_MESSAGE . $e->getMessage());
        }
    }
}
