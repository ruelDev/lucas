<?php

namespace App\Http\Controllers\Reports\LmsPosting;

use App\Constants\LoanConstants;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\JasperServerService;
use App\Traits\HasAuditLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Number;

class FinnoneQrController extends Controller
{
    use HasAuditLog;

    protected $module = 'LMS Posting - Finnone QR';

    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $dateFrom       = $request->input('date_from', '');
        $dateTo         = $request->input('date_to', '');
        $page           = $request->input('page', 1);
        $sortDirection  = in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'asc';
        $allowedSorts   = [
            'AGREEMENTNO',
            'PAYMENT_MODE',
            'RECEIPT_DATE',
            'RECEIPT_NUM',
            'RECEIPT_CHANNEL',
            'RECEIPT_AMT',
            'DEALING_BANKID'
        ];
        $sortBy         = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'id';
        $per_page       = $request->input('per_page', 10);

        $hasSearch      = $search !== '';
        $hasDateRange   = $dateFrom !== '';

        if (!$hasSearch && !$hasDateRange) {
            return inertia('reports/out-collection/lms-posting/finnone-qr/index', [
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
            ->selectRaw("
                agreementNumber AS AGREEMENTNO,
                'C' AS PAYMENT_MODE,
                arDate AS RECEIPT_DATE,
                arNumber AS RECEIPT_NUM,
                1200 AS RECEIPT_CHANNEL,
                arAmount AS RECEIPT_AMT,
                " . LoanConstants::companyCodeCase('agreementNumber') . " AS DEALING_BANKID
            ")
            ->where('source', 'finnone')
            ->where('receiptType', 'QR')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('deposits', function ($q) use ($search) {
                        $q->where('makerId', 'LIKE', "%{$search}%");
                    });
                });
            })
            ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('arDate', [$dateFrom, $dateTo]);
            })
            ->when($dateFrom && !$dateTo, function ($q) use ($dateFrom) {
                $q->where('arDate', '>=', $dateFrom);
            })
            ->orderBy($sortBy, $sortDirection)
            ->get();


        $data = $payments->map(function ($item) {
            return [
                'AGREEMENTNO'       => $item->AGREEMENTNO,
                'PAYMENT_MODE'      => $item->PAYMENT_MODE,
                'RECEIPT_DATE'      => $item->RECEIPT_DATE,
                'RECEIPT_NUM'       => $item->RECEIPT_NUM,
                'RECEIPT_CHANNEL'   => $item->RECEIPT_CHANNEL,
                'RECEIPT_AMT'       => $item->RECEIPT_AMT,
                'DEALING_BANKID'    => $item->DEALING_BANKID,
            ];
        });

        $total = $data->count();
        $paginatedData = $data->slice(($page - 1) * $per_page, $per_page)->values();
        $paginator = new LengthAwarePaginator($paginatedData, $total, $per_page, $page);

        return inertia('reports/out-collection/lms-posting/finnone-qr/index', [
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
        $reportPath = '/Document_report/LMS_Posting/Finnone_QR_report';

        $filename = 'OCQR_BMI ' . now()->format('mdy') . '_FINNONE_C';

        $params = array_filter([
            'search'        => $request->input('search'),
            'dateFrom'      => $request->input('date_from'),
            'dateTo'        => $request->input('date_to'),
        ], fn($value) => !is_null($value));

        $this->auditLogs('EXPORTED: ' . $filename, $this->module);

        return $jasperServerService->generateExcelReport($reportPath, $params, 'finnone-qr', $filename);
    }
}
