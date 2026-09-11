<?php

namespace App\Http\Controllers\Reports\LmsPosting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\JasperServerService;
use App\Traits\HasAuditLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Number;

class NewgenQrUaController extends Controller
{
    use HasAuditLog;

    protected $module = 'LMS Posting - Newgen QR';

    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $dateFrom       = $request->input('date_from', '');
        $dateTo         = $request->input('date_to', '');
        $page           = $request->input('page', 1);
        $sortDirection  = in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'asc';
        $allowedSorts   = [
            'LOAN_NO',
            'RECEIPT_MODE',
            'INSTRUMENT_NO',
            'BANK_ID',
            'BRANCH_ID',
            'BANK_ACCOUNT',
            'RECEIPT_DATE',
            'INSTRUMENT_DATE',
            'RECEIPT_AMOUNT',
            'TDS_AMOUNT',
            'RECEIPT_NO',
            'DEFAULT_BRANCH',
            'DEPOSIT_BANK',
            'DEPOSIT_BANK_BRANCH',
            'DEPOSIT_BANK_ACCOUNT',
            'MAKER_REMARKS',
            'MIS_NUMBER',
            'CUSTOMER_NAME',
            'RECEIVED_FROM',
            'ACK_RECEIPT_NO',
            'PDC_FLAG',
        ];
        $sortBy         = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'id';
        $per_page       = $request->input('per_page', 10);

        $hasSearch      = $search !== '';
        $hasDateRange   = $dateFrom !== '';

        if (!$hasSearch && !$hasDateRange) {
            return inertia('reports/out-collection/lms-posting/newgen-qr-ua/index', [
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
                agreementNumber AS LOAN_NO,
                'C' RECEIPT_MODE,
                '' INSTRUMENT_NO,
                '' BANK_ID,
                '' BRANCH_ID,
                '' BANK_ACCOUNT,
                DATE_FORMAT(arDate, '%m/%d/%Y') AS RECEIPT_DATE,
                '' INSTRUMENT_DATE,
                arAmount AS RECEIPT_AMOUNT,
                0 TDS_AMOUNT,
                'AUTO' RECEIPT_NO,
                'HEAD OFFICE' DEFAULT_BRANCH,
                22 DEPOSIT_BANK,
                24 DEPOSIT_BANK_BRANCH,
                'UNKNOWNCASH' DEPOSIT_BANK_ACCOUNT,
                'OUT COLLECTION' MAKER_REMARKS,
                misNumber AS MIS_NUMBER,
                customerName AS CUSTOMER_NAME,
                1200 RECEIVED_FROM,
                arNumber AS ACK_RECEIPT_NO,
                'N' PDC_FLAG
            ")
            ->where('source', 'newgen')
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
                'LOAN_NO'               => $item->LOAN_NO,
                'RECEIPT_MODE'          => $item->RECEIPT_MODE,
                'INSTRUMENT_NO'         => $item->INSTRUMENT_NO,
                'BANK_ID'               => $item->BANK_ID,
                'BRANCH_ID'             => $item->BRANCH_ID,
                'BANK_ACCOUNT'          => $item->BANK_ACCOUNT,
                'RECEIPT_DATE'          => $item->RECEIPT_DATE,
                'INSTRUMENT_DATE'       => $item->INSTRUMENT_DATE,
                'RECEIPT_AMOUNT'        => $item->RECEIPT_AMOUNT,
                'TDS_AMOUNT'            => $item->TDS_AMOUNT,
                'RECEIPT_NO'            => $item->RECEIPT_NO,
                'DEFAULT_BRANCH'        => $item->DEFAULT_BRANCH,
                'DEPOSIT_BANK'          => $item->DEPOSIT_BANK,
                'DEPOSIT_BANK_BRANCH'   => $item->DEPOSIT_BANK_BRANCH,
                'DEPOSIT_BANK_ACCOUNT'  => $item->DEPOSIT_BANK_ACCOUNT,
                'MAKER_REMARKS'         => $item->MAKER_REMARKS,
                'MIS_NUMBER'            => $item->MIS_NUMBER,
                'CUSTOMER_NAME'         => $item->CUSTOMER_NAME,
                'RECEIVED_FROM'         => $item->RECEIVED_FROM,
                'ACK_RECEIPT_NO'        => $item->ACK_RECEIPT_NO,
                'PDC_FLAG'              => $item->PDC_FLAG,
            ];
        });

        $total = $data->count();
        $paginatedData = $data->slice(($page - 1) * $per_page, $per_page)->values();
        $paginator = new LengthAwarePaginator($paginatedData, $total, $per_page, $page);

        return inertia('reports/out-collection/lms-posting/newgen-qr-ua/index', [
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
        $reportPath = '/Document_report/LMS_Posting/Newgen_QR_report';

        $filename = 'OCQR_BMI ' . now()->format('mdy') . '_NEWGEN_C';

        $params = array_filter([
            'search'        => $request->input('search'),
            'dateFrom'      => $request->input('date_from'),
            'dateTo'        => $request->input('date_to'),
        ], fn($value) => !is_null($value));

        $this->auditLogs('EXPORTED: ' . $filename, $this->module);

        return $jasperServerService->generateExcelReport($reportPath, $params, 'newgen-qr', $filename);
    }
}
