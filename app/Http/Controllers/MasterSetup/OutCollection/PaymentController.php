<?php

namespace App\Http\Controllers\MasterSetup\OutCollection;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutCollection\PaymentRequest;
use App\Models\Deposit;
use App\Models\Payment;
use App\Traits\HasAuditLog;
use App\Traits\HasInfoLogChannel;
use App\Traits\HasLogContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class PaymentController extends Controller
{
    use HasInfoLogChannel, HasLogContext, HasAuditLog;

    protected $module = 'Out Collection - Receipt Posting';
    protected $log;

    public function __construct()
    {
        $this->log = $this->getInfoLogChannel('out_collection');
    }

    public function index(Request $request, Deposit $deposit)
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts   = [
            'status',
            'makerId',
            'bankName',
            'makerName',
            'depositDate',
            'depositAmount',
            'referenceNumber',
            'depositoryRemarks',
        ];

        $sortBy = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'id';
        $per_page = $request->input('per_page', 10);

        $paymentQuery = Payment::where('deposit_id', $deposit->id);


        $payment = $paymentQuery
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('referenceNumber', 'LIKE', "%{$search}%")
                        ->orWhere('agreementNumber', 'LIKE', "%{$search}%")
                        ->orWhere('misNumber', 'LIKE', "%{$search}%")
                        ->orWhere('customerName', 'LIKE', "%{$search}%")
                        ->orWhere('arNumber', 'LIKE', "%{$search}%")
                        ->orWhere('makerName', 'LIKE', "%{$search}%")
                        ->orWhere('paymentType', 'LIKE', "%{$search}%")
                        ->orWhere('aoc', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy($sortBy, $sortDirection)
            ->get()
            ->map(fn($item) => [
                'id'                => $item->id,
                'deposit_id'        => $item->deposit_id,
                'makerId'           => $item->makerId,
                'makerName'         => $item->makerName,
                'agreementNumber'   => $item->agreementNumber,
                'referenceNumber'   => $item->referenceNumber,
                'misNumber'         => $item->misNumber,
                'customerName'      => $item->customerName,
                'aoc'               => $item->aoc,
                'arDate'            => $item->arDate,
                'arNumber'          => $item->arNumber,
                'arAmount'          => $item->arAmount,
                'paymentType'       => $item->paymentType,
                'npaStage'          => $item->npaStage,
                'reason'            => $item->reason,
                'status'            => $item->status,
                'remarks'           => $item->remarks,
                'source'            => $item->source,
            ]);

        $totalArAmount = $paymentQuery->sum('arAmount');
        $total = $deposit->depositAmount + $deposit->depositCharge;
        $remaining = $deposit->depositAmount - $totalArAmount;

        $deposits = [
            'id'                => $deposit->id,
            'makerId'           => $deposit->makerId,
            'makerName'         => $deposit->makerName,
            'bank'              => $deposit->banks?->name,
            'bank_abbreviation' => $deposit->banks?->abbreviation,
            'bank_code'         => $deposit->banks?->code,
            'referenceNumber'   => $deposit->referenceNumber,
            'depositDate'       => $deposit->depositDate,
            'depositAmount'     => $deposit->depositAmount,
            'depositCharge'     => $deposit->depositCharge,
            'depositSlip'       => $deposit->depositSlip,
            'status'            => $deposit->status,
            'total'             => $total,
            'remaining'         => $remaining,
            'created_at'        => $deposit->created_at
        ];

        $sorted = $sortDirection === 'asc'
            ? $payment->sortBy(fn($item) => strtolower(data_get($item, $sortBy)))->values()
            : $payment->sortByDesc(fn($item) => strtolower(data_get($item, $sortBy)))->values();

        //paginate
        $total = $sorted->count();
        $payment = $sorted->slice(($page - 1) * $per_page, $per_page)->values();

        $paginator = new LengthAwarePaginator($payment, $total, $per_page, $page);

        $items = [
            'deposits' => $deposits,
            'payments' => $payment,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => [
                'search' => $search
            ],
            'sort' => [
                'column' => $sortBy,
                'direction' => $sortDirection
            ]
        ];

        return inertia("main-modules/out-collection/view-deposit/index", $items);
    }

    // Quick Receipt
    public function createQuickReceipt(Request $request)
    {
        $accountDetails = $request->data;
        $maker          = $request->maker;

        $query = Payment::with('deposits')->where('deposit_id', $maker['id']);

        $currentTotal   = $query->sum('arAmount');
        $newTotal       = $currentTotal + $request->arAmount;

        $referenceNumber = $this->getReferenceNumber($maker, $query);

        $data = [
            'id'                => $maker['id'],
            'makerId'           => $maker['makerId'],
            'makerName'         => $maker['makerName'],
            'bank'              => $maker['bank'],
            'depositDate'       => $maker['depositDate'],
            'depositAmount'     => $maker['depositAmount'],
            'referenceNumber'   => $referenceNumber,
            'agreementNumber'   => $accountDetails['agreementNumber'],
            'misNumber'         => $accountDetails['misNumber'],
            'customerName'      => $accountDetails['customerName'],
            'aoc'               => $accountDetails['aoc'],
            'arNumber'          => $accountDetails['arNumber'],
            'arAmount'          => $accountDetails['arAmount'],
            'paymentType'       => $accountDetails['paymentType'],
            'arDate'            => Carbon::parse($accountDetails['arDate'])->format('Y-m-d'),
            'npaStage'          => $accountDetails['npaStage'],
            'remarks'           => $accountDetails['remarks'],
            'source'            => $accountDetails['source'],
            'company'           => $accountDetails['company'] ?? null,
            'total'             => $newTotal,
            'hasDeposits'       => $query->exists()
        ];

        return inertia('main-modules/out-collection/view-deposit/actions/create-quick-receipt', [
            'accountDetails' => $data
        ]);
    }

    public function storeQuickReceipt(PaymentRequest $receiptRequest)
    {
        $validated = $receiptRequest->validated();

        $context = $this->getLogContext($receiptRequest, [
            'deposit_id' => $receiptRequest->deposit_id,
            'validated_data'     => $validated,
        ]);

        $this->log->info('Quick receipt store initiated', $context);

        DB::beginTransaction();

        try {
            $validated['receiptType'] = 'QR';

            $payment = Payment::create($validated);

            DB::commit();

            $this->log->info('Quick receipt store successful', [
                'deposit_id' => $payment->deposit_id,
                ...$context,
            ]);

            $this->auditLogs('CREATED: QUICK RECEIPT', $this->module, $payment);

            return redirect("/out-collection/$receiptRequest->deposit_id/view-deposit")
                ->with('success', 'Payment successfully created');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->log->error('Quick receipt store failed', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while creating the payment.']);
        }
    }

    // Unapplied Receipt
    public function createUnappliedReceipt(Request $request)
    {
        $maker = $request->maker;
        $data = $request->data;

        $query = Payment::where('deposit_id', $maker['id']);

        $currentTotal = $query->sum('arAmount');
        $newTotal = $currentTotal + ($request->arAmount ?? 0);

        $referenceNumber = $this->getReferenceNumber($maker, $query);

        $accountDetails = [
            'id'                => $maker['id'],
            'makerId'           => $maker['makerId'],
            'makerName'         => $maker['makerName'],
            'bank'              => $maker['bank'],
            'depositDate'       => $maker['depositDate'],
            'depositAmount'     => $maker['depositAmount'],
            'referenceNumber'   => $referenceNumber,

            'agreementNumber'   => $data['agreementNumber'] ?? null,
            'misNumber'         => $data['misNumber'] ?? null,
            'customerName'      => $data['customerName'] ?? null,
            'aoc'               => $data['aoc'] ?? null,
            'arNumber'          => $data['arNumber'] ?? null,
            'arAmount'          => $data['arAmount'] ?? null,
            'paymentType'       => $data['paymentType'] ?? null,
            'arDate'            => !empty($data['arDate'])
                ? Carbon::parse($data['arDate'])->format('Y-m-d')
                : null,
            'npaStage'          => $data['npaStage'] ?? null,
            'remarks'           => $data['remarks'] ?? null,
            'source'            => $data['source'] ?? null,
            'company'           => $data['company'] ?? null,

            'total'             => $newTotal,
            'hasDeposits'       => $query->exists(),
        ];

        if (!$data) {
            $accountDetails = [
                'id'                => $maker['id'],
                'makerId'           => $maker['makerId'],
                'makerName'         => $maker['makerName'],
                'depositAmount'     => Number::parseFloat($maker['depositAmount']),
                'referenceNumber'   => $referenceNumber,
                'agreementNumber'   => $request->agreement_number,
                'total'             => $newTotal,
                'hasDeposits'       => $query->exists()
            ];
        }

        return inertia(
            'main-modules/out-collection/view-deposit/actions/create-unapplied-receipt',
            ['accountDetails' => $accountDetails]
        );
    }

    public function storeUnappliedReceipt(PaymentRequest $request)
    {
        $validated = $request->validated();

        $context = $this->getLogContext($request, [
            'deposit_id'        => $request->deposit_id,
            'validated_data'    => $validated,
        ]);

        $this->log->info('Unapplied receipt store initiated', $context);

        DB::beginTransaction();

        try {
            $validated['receiptType'] = 'UA';

            $payment = Payment::create($validated);

            DB::commit();

            $this->log->info('Unapplied receipt store successful', [
                ...$context,
                'deposit_id' => $payment->deposit_id,
            ]);

            $this->auditLogs('CREATED: UNAPPLIED RECEIPT', $this->module, $payment);

            return redirect("/out-collection/$request->deposit_id/view-deposit")
                ->with('success', 'Payment successfully created');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->log->error('Unapplied receipt store failed', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while creating the payment.']);
        }
    }

    // Update Receipt
    public function editReceipt(Deposit $deposit, Payment $payment)
    {
        return inertia('main-modules/out-collection/view-deposit/actions/edit-receipt', ['payments' => $payment]);
    }

    public function updateReceipt(Deposit $deposit, PaymentRequest $paymentRequest, Payment $payment)
    {
        $validated = $paymentRequest->validated();

        $context = $this->getLogContext($paymentRequest, [
            'deposit_id'         => $validated['deposit_id'],
            'validated_data'     => $validated,
        ]);

        $this->log->info('Payment receipt update initiated', $context);

        DB::beginTransaction();

        try {
            if ($validated['status'] == 'Sent Back') {
                $validated['status'] = 'Sent to Author';
            }

            $payment->update($validated);

            DB::commit();

            $this->log->info('Payment receipt update successful', $context);

            $this->auditLogs('UPDATED: ACKNOWLEDGEMENT RECEIPT', $this->module, $payment);

            return redirect("/out-collection/$paymentRequest->deposit_id/view-deposit")
                ->with('success', 'Payment successfully updated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->log->error('Payment receipt update failed', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while updating the payment: ' . $e->getMessage()]);
        }
    }

    // Destroy Receipt
    public function destroy(Deposit $deposit, Payment $payment)
    {
        $context = $this->getLogContext(request(), [
            'id'                => $payment->id,
            'deposit_id'        => $payment->deposit_id,
            'deposit_amount'    => $payment->depositAmount,
            'deposit_date'      => $payment->depositDate,
            'status'            => $payment->status,
        ]);

        $this->log->info('Payment receipt delete initiated', $context);

        DB::beginTransaction();

        try {
            $payment->delete();

            DB::commit();

            $this->log->info('Payment receipt deleted successfully', $context);

            $this->auditLogs('DELETED: ACKNOWLEDGEMENT RECEIPT', $this->module, $payment);

            return redirect("/out-collection/$payment->deposit_id/view-deposit")
                ->with('success', 'Payment deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->log->error('Payment receipt delete failed', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while deleting the payment: ' . $e->getMessage()]);
        }
    }

    private function getReferenceNumber(array $maker, object $query)
    {
        $prefix = $maker['bank_abbreviation'] . '-' . $maker['referenceNumber'] . '-' . Carbon::parse($maker['depositDate'])->format('Ymd') . '-';

        $lastRecord = $query
            ->orderBy('referenceNumber', 'desc')
            ->value('referenceNumber');

        if ($lastRecord) {
            $lastIncrement = (int) str_replace($prefix, '', $lastRecord);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        return $prefix . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    public function viewFile(Deposit $deposit)
    {
        if (!$deposit->depositSlip) {
            abort(404, 'No deposit slip on record.');
        }

        $path = storage_path('app/public/out-collection/deposit-slips/' . $deposit->depositSlip);

        if (!file_exists($path)) {
            abort(404, 'File not found: ' . $path);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'pdf'         => 'application/pdf',
            default       => 'application/octet-stream',
        };

        $this->auditLogs('VIEWED: ACKNOWLEDGEMENT RECEIPT', $this->module, $deposit);

        return response()->file($path, ['Content-Type' => $mime]);
    }
}
