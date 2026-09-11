<?php

namespace App\Http\Controllers\MasterSetup\OutCollection;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutCollection\DepositRequest;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Deposit;
use App\Traits\HasAuditLog;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\HasInfoLogChannel;
use App\Traits\HasLogContext;

class DepositController extends Controller
{
    use HasDataTable, HasInfoLogChannel, HasLogContext, HasAuditLog;

    protected $defaultSentToAuthorStatus = "Sent to Author";
    protected $defaultSentBackStatus = "Sent Back";
    protected $defaultAuthorizedStatus = "Authorized";
    protected $defaultDraftStatus = "Draft";
    protected $module = 'Out Collection - Receipt Posting';

    protected $log;

    public function __construct()
    {
        $this->log = $this->getInfoLogChannel('out_collection');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts   = [
            'status',
            'bankName',
            'makerName',
            'depositDate',
            'depositAmount',
            'referenceNumber',
            'depositoryRemarks',
        ];
        $sortBy = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'id';
        $sortColumnMap = [
            'bankName' => 'banks.name',
        ];
        $sortColumn = $sortColumnMap[$sortBy] ?? "deposits.{$sortBy}";

        $per_page = $request->input('per_page', 10);
        $user = auth()->user();

        $isAuthorize = $user->can('out_collection.authorize');

        $query = Deposit::with('payments')
            ->leftJoin('banks', 'banks.id', '=', 'deposits.bank_id')
            ->select('deposits.*', 'banks.name as bankName');

        if ($isAuthorize) {
            $query->where('status', '!=', 'Draft')
                ->where(function ($q) use ($search) {
                    $q->where('makerId', 'LIKE', "%{$search}%")
                        ->orWhere('depositDate', 'LIKE', "%{$search}%");
                });
        } else {
            $query->where('makerId', $user->employee_id)
                ->where(function ($q) use ($search) {
                    $q->where('referenceNumber', 'LIKE', "%{$search}%")
                        ->orWhere('depositAmount', 'LIKE', "%{$search}%")
                        ->orWhere('depositDate', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%");
                });
        }

        $depositData = $query
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($per_page)
            ->map(fn($item) => [
                'id'                 => $item->id,
                'status'             => $item->status,
                'makerId'            => $item->makerId,
                'makerName'          => $item->makerName,
                'bankName'           => $item->bankName,
                'depositDate'        => $item->depositDate,
                'referenceNumber'    => $item->referenceNumber,
                'hasDeposits'        => $item->payments->isNotEmpty(),
                'depositoryRemarks'  => $item->depositoryRemarks,
                'depositAmount'      => $item->depositAmount,
            ]);

        $sorted = $sortDirection === 'asc'
            ? $depositData->sortBy(fn($item) => strtolower(data_get($item, $sortBy)))->values()
            : $depositData->sortByDesc(fn($item) => strtolower(data_get($item, $sortBy)))->values();

        //paginate
        $total = $sorted->count();
        $data = $sorted->slice(($page - 1) * $per_page, $per_page)->values();

        $paginator = new LengthAwarePaginator(
            $data,
            $total,
            $per_page,
            $page
        );

        $items = [
            'data' => $data,
            'pagination' => [
                'current_page'  => $paginator->currentPage(),
                'last_page'     => $paginator->lastPage(),
                'per_page'      => $paginator->perPage(),
                'total'         => $paginator->total(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
            ],
            'filters' => [
                'search'    => $search
            ],
            'sort' => [
                'column'    => $sortBy,
                'direction' => $sortDirection
            ]
        ];

        return inertia('main-modules/out-collection/index', $items);
    }

    public function create()
    {
        return inertia('main-modules/out-collection/actions/create', ['banks' => Bank::with('bankAccounts')->get()]);
    }

    public function store(DepositRequest $depositRequest)
    {
        $user = Auth::user();
        $makerId = $user->employee_id;
        $makerName = trim(
            $user->fname . ' ' .
                ($user->mname ? $user->mname . ' ' : '') .
                $user->lname
        );

        $validated = $depositRequest->validated();
        $context = $this->context($depositRequest, $validated, $user);

        $this->log->info('Deposit store initiated', $context);

        $bankAccount = BankAccount::find($validated['depositoryRemarks']);

        $validated['depositoryRemarks'] = $bankAccount->depository_remarks;

        if (!$bankAccount) {
            return redirect()->back()->withErrors(['depositoryRemarks' => 'Selected bank account is invalid.']);
        }

        if ($depositRequest->hasFile('depositSlip')) {

            $file = $depositRequest->file('depositSlip');

            $originalName = $file->getClientOriginalName();

            $safeName = time() . '_' . $originalName;

            $directory = storage_path('app/public/out-collection/deposit-slips');

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->storeAs('out-collection/deposit-slips', $safeName, 'public');

            $validated['depositSlip'] = $safeName;

            $this->log->info('Deposit slip uploaded', [
                'uploaded_file' => $safeName,
                ...$context,
            ]);
        }

        DB::beginTransaction();

        try {
            $data = Deposit::create([
                'status'            => 'Draft',
                'makerId'           => $makerId,
                'makerName'         => $makerName,
                ...$validated,
            ]);

            DB::commit();

            $this->log->info('Deposit created successfully', [
                'id' => $data->id,
                'status'             => 'Draft',
                'uploaded_file'      => $validated['depositSlip'],
                ...$context,
            ]);

            $this->auditLogs('CREATED', $this->module, $data);

            return to_route('out-collection.index')
                ->with(['success' => 'Deposit created successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->log->error('Deposit store failed', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create out collection ' . $e->getMessage()]);
        }
    }

    public function edit(Deposit $deposit)
    {
        return inertia('main-modules/out-collection/actions/edit', [
            'deposit' => $deposit,
            'banks' => Bank::with('bankAccounts')->get()
        ]);
    }

    public function update(DepositRequest $depositRequest, Deposit $deposit)
    {
        $user = Auth::user();

        $validated = $depositRequest->validated();

        $context = $this->context($depositRequest, $validated, $user);

        $this->log->info('Deposit update initiated', $context);

        $bankAccount = BankAccount::find($validated['depositoryRemarks']);

        $validated['depositoryRemarks'] = $bankAccount->depository_remarks;

        if (!$bankAccount) {
            return redirect()->back()->withErrors(['depositoryRemarks' => 'Selected bank account is invalid.']);
        }

        if ($depositRequest->hasFile('depositSlip')) {

            $oldPath = storage_path('app/public/out-collection/deposit-slips/' . $deposit->depositSlip);

            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            // Store new file
            $file = $depositRequest->file('depositSlip');
            $originalName = $file->getClientOriginalName();
            $safeName = time() . '_' . $originalName;

            $directory = storage_path('app/public/out-collection/deposit-slips');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->storeAs('out-collection/deposit-slips', $safeName, 'public');

            $validated['depositSlip'] = $safeName;

            $this->log->info('New deposit slip uploaded', [
                'new_file' => $validated['depositSlip'],
                ...$context,
            ]);
        } else {
            $validated['depositSlip'] = $deposit->depositSlip;

            $this->log->info('No new deposit slip provided, retaining existing file', [
                'retained_file' => $validated['depositSlip'],
                ...$context,
            ]);
        }

        DB::beginTransaction();

        try {
            $deposit->update([
                'bank_id'               => $validated['bank_id'],
                'depositSlip'           => $validated['depositSlip'] ?? $depositRequest->depositSlip,
                'depositDate'           => $validated['depositDate'],
                'depositCharge'         => $validated['depositCharge'],
                'depositAmount'         => $validated['depositAmount'],
                'referenceNumber'       => $validated['referenceNumber'],
                'depositoryRemarks'     => $validated['depositoryRemarks'],
            ]);

            DB::commit();

            $this->log->info('Deposit updated successfully', $context);

            $this->auditLogs('UPDATED', $this->module, $deposit);

            return to_route('out-collection.index')->with('success', 'Deposit updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->log->error('Deposit update failed', [
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
                ...$context,
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to update out collection ' . $e->getMessage()]);
        }
    }

    public function destroy(Deposit $deposit)
    {
        $context = $this->getLogContext(request(), [
            'id'                    => $deposit->id,
            'maker_id'              => $deposit->makerId,
            'maker_name'            => $deposit->makerName,
            'bank_id'               => $deposit->bank_id,
            'deposit_amount'        => $deposit->depositAmount,
            'deposit_date'          => $deposit->depositDate,
            'deposit_charge'        => $deposit->depositCharge,
            'reference_number'      => $deposit->referenceNumber,
            'depository_remarks'    => $deposit->depositoryRemarks
        ]);

        $this->log->info('Deposit soft delete initiated', $context);

        try {
            $deposit->delete();

            $this->log->info('Deposit soft deleted successfully', $context);

            $this->auditLogs('DELETED', $this->module, $deposit);

            return to_route('out-collection.index')
                ->with('success', 'Deposit deleted successfully!');
        } catch (\Throwable $e) {
            $this->log->error('Deposit delete failed', [
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
                ...$context,
            ]);

            throw $e;
        }
    }

    private function context(DepositRequest $depositRequest, array $validated, object $user)
    {
        $makerId = $user->employee_id;
        $makerName = trim(
            $user->fname . ' ' .
                ($user->mname ? $user->mname . ' ' : '') .
                $user->lname
        );

        return $this->getLogContext($depositRequest, [
            'maker_id'              => $makerId,
            'maker_name'            => $makerName,
            'bank_id'               => $validated['bank_id'],
            'deposit_amount'        => $validated['depositAmount'],
            'deposit_date'          => $validated['depositDate'],
            'deposit_charge'        => $validated['depositCharge'],
            'reference_number'      => $validated['referenceNumber'],
            'depository_remarks'    => $validated['depositoryRemarks'],
        ]);
    }
}
