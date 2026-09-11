<?php

namespace App\Http\Controllers\Reports\OutCollection;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\JasperServerService;
use App\Traits\HasAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Number;

class AuthorizeController extends Controller
{
    use HasAuditLog;

    protected $module = 'Out Collection - Authorized';

    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $dateFrom       = $request->input('date_from', '');
        $dateTo         = $request->input('date_to', '');
        $page           = $request->input('page', 1);
        $sortDirection  = in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'asc';
        $allowedSorts   = [
            'agreementNumber',
            'customerName',
            'arNumber',
            'arDate',
            'arAmount',
            'makerId',
            'makeDate',
            'bank_name',
            'bankAccountNo',
            'dateDeposited',
            'amount',
            'authorizerId',
            'dateAuthor',
            'referenceNumber',
        ];

        $sortBy         = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'id';
        $sortColumnMap = [
            'bank_name'  => 'banks.name',
            'dateAuthor' => 'payments.dateAuthor',
            'dateDeposited' => 'payments.depositDate',
            'amount'     => 'payments.depositAmount', // or wherever total comes from
        ];
        $sortColumn = $sortColumnMap[$sortBy] ?? "payments.{$sortBy}";
        $per_page       = $request->input('per_page', 10);

        $hasSearch      = $search !== '';
        $hasDateRange   = $dateFrom !== '';

        if (!$hasSearch && !$hasDateRange) {
            return inertia('reports/out-collection/authorized/index', [
                'data'       => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page'    => 1,
                    'per_page'     => 10,
                    'total'        => 0,
                    'from'         => 0,
                    'to'           => 0,
                ],
                'filters' => [
                    'search'        => '',
                    'date_from'     => '',
                    'date_to'       => '',
                    'receipt_type'  => '',
                ],
                'sort' => [
                    'column'    => $sortBy,
                    'direction' => $sortDirection,
                ],
            ]);
        }

        $payments = Payment::with('deposits.banks')
            ->leftJoin('deposits', 'deposits.id', '=', 'payments.deposit_id')
            ->leftJoin('banks', 'banks.id', '=', 'deposits.bank_id')
            ->select(
                'payments.*',
                'banks.name as bank_name',
                'deposits.referenceNumber as bankAccountNo',
                'deposits.depositDate as depositDated'
            )
            ->where('payments.status', 'Authorized')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('deposits', function ($q) use ($search) {
                        $q->where('authorizerId', 'LIKE', "%{$search}%");
                    });
                });
            })
            ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('arDate', [$dateFrom, $dateTo]);
            })
            ->when($dateFrom && !$dateTo, function ($q) use ($dateFrom) {
                $q->where('arDate', '>=', $dateFrom);
            })
            ->orderBy($sortColumn, $sortDirection)
            ->get();

        $totals = $payments->groupBy('deposit_id')
            ->map(fn($group) => $group->sum('arAmount'));

        $dateFormat = 'm/d/Y';

        $data = $payments->map(function ($item) use ($totals, $dateFormat) {
            return [
                'deposit_id'        => $item->deposit_id,
                'agreementNumber'   => $item->agreementNumber,
                'customerName'      => $item->customerName,
                'arNumber'          => $item->arNumber,
                'arDate'            => Carbon::parse($item->arDate)->format($dateFormat),
                'arAmount'          => Number::parseFloat($item->arAmount),
                'makerId'           => $item->makerId,
                'makeDate'          => $item->created_at->format($dateFormat),
                'bank_name'         => $item->bank_name,
                'bankAccountNo'     => $item->bankAccountNo,
                'dateDeposited'     => Carbon::parse($item->depositDated)->format($dateFormat),
                'amount'            => Number::parseFloat($totals[$item->deposit_id]),
                'authorizerId'      => $item->authorizerId,
                'dateAuthor'        => Carbon::parse($item->dateAuthor)->format($dateFormat),
                'referenceNumber'   => $item->referenceNumber,
            ];
        });

        $total = $data->count();
        $paginatedData = $data->slice(($page - 1) * $per_page, $per_page)->values();
        $paginator = new LengthAwarePaginator($paginatedData, $total, $per_page, $page);

        return inertia('reports/out-collection/authorized/index', [
            'data'       => $paginatedData,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'filters' => [
                'search'        => $search,
                'date_from'     => $dateFrom,
                'date_to'       => $dateTo,
            ],
            'sort' => [
                'column'    => $sortBy,
                'direction' => $sortDirection,
            ],
        ]);
    }

    public function export(Request $request, JasperServerService $jasperServerService)
    {
        $reportPath = '/Document_report/Receipt_Posting/authorized_report';

        $filename = 'OC_AUTHORIZED_' . now()->format('mdy');

        $params = array_filter([
            'search'        => $request->input('search'),
            'dateFrom'      => $request->input('date_from'),
            'dateTo'        => $request->input('date_to'),
        ], fn($value) => !is_null($value));

        $this->auditLogs('EXPORTED: ' . $filename, $this->module);

        return $jasperServerService->generateExcelReport($reportPath, $params, 'authorized', $filename);
    }
}
