<?php

namespace App\Http\Controllers\Reports\OutCollection;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Services\JasperServerService;
use App\Traits\HasAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Illuminate\Pagination\LengthAwarePaginator;

class DailyCollectionController extends Controller
{
    use HasAuditLog;

    protected $module = 'Out Collection - Daily Collection';

    public function index(Request $request)
    {
        $search         = $request->input('search', '');
        $dateFrom       = $request->input('date_from', '');
        $dateTo         = $request->input('date_to', '');
        $receiptType    = $request->input('receipt_type');
        $page           = $request->input('page', 1);
        $sortDirection  = in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'asc';
        $allowedSorts   = [
            'agreementNumber',
            'misNumber',
            'arNumber',
            'arDate',
            'arAmount',
            'makerId',
            'created_at'
        ];
        $sortBy         = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'id';
        $per_page       = $request->input('per_page', 10);

        $hasSearch      = $search !== '';
        $hasDateRange   = $dateFrom !== '';
        $hasReceiptType = $receiptType !== null;

        if (!$hasSearch && !$hasDateRange && !$hasReceiptType) {
            return inertia('reports/out-collection/daily-collection/index', [
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

        $payments = Payment::query()
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
            ->when($receiptType, function ($q) use ($receiptType) {
                $q->where('receiptType', $receiptType);
            })
            ->orderBy($sortBy, $sortDirection)
            ->get();

        $dateFormat = 'm/d/Y';

        $data = $payments->map(fn($item) => [
            'agreementNumber'    => $item->agreementNumber,
            'misNumber'          => $item->misNumber,
            'referenceNumber'    => $item->referenceNumber,
            'arNumber'           => $item->arNumber,
            'arDate'             => Carbon::parse($item->arDate)->format($dateFormat),
            'arAmount'           => Number::parseFloat($item->arAmount),
            'makerId'            => $item->makerId,
            'created_at'         => $item->created_at->format($dateFormat),
        ]);

        $total = $data->count();
        $paginatedData = $data->slice(($page - 1) * $per_page, $per_page)->values();
        $paginator = new LengthAwarePaginator($paginatedData, $total, $per_page, $page);

        return inertia('reports/out-collection/daily-collection/index', [
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
                'receipt_type'  => $receiptType,
            ],
            'sort' => [
                'column'    => $sortBy,
                'direction' => $sortDirection,
            ],
        ]);
    }

    public function export(Request $request, JasperServerService $jasperServerService)
    {
        $reportPath = '/Document_report/Receipt_Posting/daily_collection_report';

        $filename = 'OC_DCR_' . now()->format('mdy');

        $params = array_filter([
            'search'        => $request->input('search'),
            'dateFrom'      => $request->input('date_from'),
            'dateTo'        => $request->input('date_to'),
            'receiptType'   => $request->input('receipt_type'),
        ], fn($value) => !is_null($value));

        $this->auditLogs('EXPORTED: ' . $filename, $this->module);

        return $jasperServerService->generatePdfReport($reportPath, $params, 'daily_collection_report', $filename);
    }
}
