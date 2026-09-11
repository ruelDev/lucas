<?php

namespace App\Http\Controllers\MasterSetup\BankManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterSetup\BankManagementRequest;
use App\Models\Bank;
use App\Traits\HasAuditLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BankManagementController extends Controller
{
    use HasAuditLog;

    protected $module = 'Bank Management';
    private const ERROR_MESSAGE = 'Something went wrong. Please try again.';

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $sortDirection = $request->input('direction', 'desc');
        $sortBy = $request->input('sort', 'created_at');
        $per_page = $request->input('per_page', 10);
        $query = Bank::query();

        $banks = $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%");
        })
            ->orderBy($sortBy, $sortDirection)
            ->get();

        $sorted = $sortDirection === 'asc'
            ? $banks->sortBy(fn($item) => strtolower(data_get($item, $sortBy)))->values()
            : $banks->sortByDesc(fn($item) => strtolower(data_get($item, $sortBy)))->values();

        $total = $sorted->count();
        $banks = $sorted->slice(($page - 1) * $per_page, $per_page)->values();

        $paginator = new LengthAwarePaginator(
            $banks,
            $total,
            $per_page,
            $page
        );

        $items = [
            'data' => $banks->map(fn($bank) => [
                'id'            => $bank->id,
                'name'          => $bank->name,
                'abbreviation'  => $bank->abbreviation,
                'description'   => $bank->description,
                'created_at'    => $bank->created_at
            ]),
            'pagination' => [
                'current_page'  => $paginator->currentPage(),
                'last_page'     => $paginator->lastPage(),
                'per_page'      => $paginator->perPage(),
                'total'         => $paginator->total(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
            ],
            'filters' => ['search' => $search],
            'sort' => ['column'    => $sortBy, 'direction' => $sortDirection]
        ];

        return inertia('master-setup/bank-management/index', $items);
    }

    public function create()
    {
        return inertia('master-setup/bank-management/actions/create');
    }

    public function store(BankManagementRequest $request)
    {
        try {
            $bank = Bank::create($request->validated());

            $this->auditLogs('CREATED', $this->module, $bank);

            return to_route('bank-management.index')->with('success', 'Bank added successfully.');
        } catch (QueryException $e) {
            return to_route('bank-management.index')->with('error', 'Failed to save bank. Please try again.');
        } catch (\Exception $e) {
            return to_route('bank-management.index')->with('error', self::ERROR_MESSAGE . $e->getMessage());
        }
    }

    public function edit(Bank $bank)
    {
        return inertia('master-setup/bank-management/actions/edit', ['bank' => $bank]);
    }

    public function update(BankManagementRequest $request, Bank $bank)
    {
        try {
            $bank->update($request->validated());

            $this->auditLogs('UPDATED', $this->module, $bank);

            return to_route('bank-management.index')->with('success', 'Bank updated successfully.');
        } catch (QueryException $e) {
            return to_route('bank-management.index')->with('error', 'Failed to update bank. Please try again.');
        } catch (\Exception $e) {
            return to_route('bank-management.index')->with('error', self::ERROR_MESSAGE . $e->getMessage());
        }
    }

    public function destroy(Bank $bank)
    {
        try {
            $bank->delete();

            $this->auditLogs('DELETED', $this->module, $bank);

            return to_route('bank-management.index')->with('success', 'Bank deleted successfully.');
        } catch (QueryException $e) {
            return to_route('bank-management.index')->with('error', 'Failed to delete bank. Please try again.');
        } catch (\Exception $e) {
            return to_route('bank-management.index')->with('error', self::ERROR_MESSAGE . $e->getMessage());
        }
    }
}
