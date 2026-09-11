<?php

namespace App\Http\Controllers\MasterSetup;

use App\Http\Controllers\Controller;
use App\Models\LoanDetailsFinnone;
use App\Models\LoanDetailsNewgenBfc;
use App\Models\LoanDetailsNewgenBmi;
use App\Services\JasperServerService;
use App\Models\Signatory;
use App\Traits\HasAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CertificateofFullpaymentController extends Controller
{
    use HasAuditLog;

    protected $module = 'CERTIFICATE OF FULL PAYMENT';


    public function index(Request $request)
    {
        Log::channel('cfp')->info('Accessed the Certificate of Fullpayment', ['user_id' => Auth::id()]);

        $search = strtoupper($request->input('search', ''));
        $page = $request->input('page', 1);
        $sortDirection = $request->input('direction', 'desc');
        $sortBy = $request->input('sort', 'agreementnumber');
        $per_page = $request->input('per_page', 100);
        $company = Auth::user()->company;
        try {

            $relationship = [
                'customersdetails',
                'addressdetails',
                'motordetails'
            ];

            $company = Auth::user()->company;

            // CONFIG
            $config = [
                'BMI' => [
                    'prefix' => 'TOM',
                    'schemeid' => ['131', '135', '900'],
                    'model' => LoanDetailsNewgenBmi::class,
                ],
                'BFC' => [
                    'prefix' => 'TOB',
                    'schemeid' => ['906', '132', '138'],
                    'model' => LoanDetailsNewgenBfc::class,
                ]
            ];

            // DETERMINE COMPANIES
            $companies = in_array($company, ['BOTH', 'DEALER'])
                ? ['BMI', 'BFC']
                : [$company];

            // MERGE SCHEME IDS (FOR FINNONE)
            $allSchemeIds = collect($companies)
                ->flatMap(fn($comp) => $config[$comp]['schemeid'])
                ->unique()
                ->values()
                ->toArray();

            // EMPTY SEARCH
            if (!$search) {
                $results2 = [
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => $per_page,
                        'total' => 0,
                        'from' => null,
                        'to' => null,
                    ],
                    'filters' => [
                        'search' => $search
                    ],
                    'sort' => [
                        'column' => $sortBy,
                        'direction' => $sortDirection
                    ]
                ];
            } else {

                $results = collect();

                // -----------------------------------
                // DETECT NEWGEN COMPANY BY PREFIX
                // -----------------------------------
                $matchedCompany = null;

                foreach ($companies as $comp) {
                    if (str_starts_with($search, $config[$comp]['prefix'])) {
                        $matchedCompany = $comp;
                        break;
                    }
                }

                // -----------------------------------
                // NEWGEN DATA (BMI / BFC)
                // -----------------------------------
                if ($matchedCompany) {

                    $model = $config[$matchedCompany]['model'];

                    $data = $model::with($relationship)
                        ->where('LOAN_NO', 'LIKE', "{$search}%")
                        ->where('REC_STATUS', 'C')
                        ->whereIn('NPA_FLAG', ['PDNP', 'PDP', 'REGULAR'])
                        ->limit(10)
                        ->get()
                        ->map(fn($customer) => [
                            'agreementnumber' => $customer->LOAN_NO,
                            'customername'    => $customer->customersdetails->customer_name ?? 'N/A',
                            'address'         => $customer->addressdetails->ADDRESS_LINE1 ?? 'N/A'
                        ]);

                    $results = $results->merge($data);
                } else {

                    // -----------------------------------
                    // FINNONE DATA
                    // -----------------------------------
                    $data = LoanDetailsFinnone::with([
                        'addressdetailsfinnone' => function ($query) {
                            $query->where('mailingaddress', 'Y');
                        },
                        'customerdetailsfinnone',
                        'assetdetailsfinnone'
                    ])
                        ->where('agreementno', 'LIKE', "{$search}%")
                        ->whereIn('schemeid', $allSchemeIds)
                        ->where('status', 'C')
                        ->whereIn('NPA_STAGEID', ['REGULAR', 'NPA'])
                        ->limit(10)
                        ->get()
                        ->map(fn($customer) => [
                            'agreementnumber' => $customer->agreementno,
                            'customername'    => $customer->customerdetailsfinnone->customername ?? 'N/A',
                            'address'         => $customer->addressdetailsfinnone->address ?? 'N/A'
                        ]);

                    $results = $results->merge($data);
                }

                // -----------------------------------
                // SORTING
                // -----------------------------------
                $sorted = $sortDirection === 'asc'
                    ? $results->sortBy(fn($item) => strtolower(data_get($item, $sortBy)))->values()
                    : $results->sortByDesc(fn($item) => strtolower(data_get($item, $sortBy)))->values();

                // -----------------------------------
                // PAGINATION
                // -----------------------------------
                $total = $sorted->count();

                $results = $sorted
                    ->slice(($page - 1) * $per_page, $per_page)
                    ->values();

                $paginator = new LengthAwarePaginator(
                    $results,
                    $total,
                    $per_page,
                    $page
                );

                // -----------------------------------
                // RESPONSE
                // -----------------------------------
                $results2 = [
                    'data' => $results,
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page'    => $paginator->lastPage(),
                        'per_page'     => $paginator->perPage(),
                        'total'        => $paginator->total(),
                        'from'         => $paginator->firstItem(),
                        'to'           => $paginator->lastItem(),
                    ],
                    'filters' => [
                        'search' => $search
                    ],
                    'sort' => [
                        'column' => $sortBy,
                        'direction' => $sortDirection
                    ]
                ];
            }
            return inertia('main-modules/cfp/index', $results2);
        } catch (\Throwable $th) {
            Log::channel('cfp')->info($th);
        }
    }

    public function show($customer)
    {
        Log::channel('cfp')->info('Accessed Agreement Number on the Certificate of Fullpayment', ['agreement_number' => $customer]);
        // RELATIONSHIPS
        $finnoneRelationship = [
            'customerdetailsfinnone',
            'addressdetailsfinnone',
            'assetdetailsfinnone.assetdetails'
        ];

        $relationship = [
            'customersdetails',
            'addressdetails',
            'motordetails'
        ];

        // USER COMPANY
        $company = Auth::user()->company;

        // CONFIG
        $config = [
            'BMI' => [
                'prefix' => 'TOM',
                'schemeid' => ['131', '135', '900'],
                'model' => LoanDetailsNewgenBmi::class,
            ],
            'BFC' => [
                'prefix' => 'TOB',
                'schemeid' => ['906', '132', '138'],
                'model' => LoanDetailsNewgenBfc::class,
            ]
        ];

        // DETERMINE Companies
        $companies = in_array($company, ['BOTH', 'DEALER'])
            ? ['BMI', 'BFC']
            : [$company];

        // BASE FINNONE QUERY
        $finnoneData = LoanDetailsFinnone::with($finnoneRelationship);

        // ----------------------------------
        // DETECT PREFIX MATCH
        // ----------------------------------
        $matchedCompany = null;

        foreach ($companies as $comp) {
            if (Str::startsWith($customer, $config[$comp]['prefix'])) {
                $matchedCompany = $comp;
                break;
            }
        }

        // ----------------------------------
        // FETCH DATA
        // ----------------------------------

        if ($matchedCompany) {

            // NEWGEN (BMI or BFC based on prefix)
            $model = $config[$matchedCompany]['model'];

            $selectedCustomer = $model::with($relationship)
                ->where('LOAN_NO', $customer)
                ->where('REC_STATUS', 'C')
                ->whereIn('NPA_FLAG', ['PDNP', 'PDP', 'REGULAR'])
                ->get()
                ->map(function ($customer) {
                    return [
                        'loan_no' => $customer->LOAN_NO,
                        'fullname' => $customer->customersdetails->customer_name ?? 'N/A',
                        'address_details' => $customer->addressdetails->ADDRESS_LINE1 ?? 'N/A',
                        'brand' => $customer->motordetails->ASMAN ?? 'N/A',
                        'model' => $customer->motordetails->ASMK ?? 'N/A',
                        'engineno' => $customer->motordetails->ENO ?? 'N/A',
                        'chassisno' => $customer->motordetails->ASCHNO ?? 'N/A',
                        'color' => $customer->motordetails->COLOR ?? 'N/A'
                    ];
                });
        } else {

            // FINNONE (merge scheme IDs for BOTH / DEALER)
            $allSchemeIds = collect($companies)
                ->flatMap(fn($comp) => $config[$comp]['schemeid'])
                ->unique()
                ->values()
                ->toArray();

            $selectedCustomer = $finnoneData
                ->where('agreementno', $customer)
                ->whereIn('schemeid', $allSchemeIds)
                ->where('status', 'C')
                ->whereIn('NPA_STAGEID', ['REGULAR', 'NPA'])
                ->get()
                ->map(function ($customer) {
                    return [
                        'loan_no' => $customer->agreementno,
                        'fullname' => $customer->customerdetailsfinnone->customername ?? 'N/A',
                        'address_details' => $customer->addressdetailsfinnone->address ?? 'N/A',
                        'brand' => $customer->assetdetailsfinnone->manufacturerdesc ?? 'N/A',
                        'model' => $customer->assetdetailsfinnone->make ?? 'N/A',
                        'engineno' => $customer->assetdetailsfinnone->assetdetails->enginenum ?? 'N/A',
                        'chassisno' => $customer->assetdetailsfinnone->assetdetails->chasisnum ?? 'N/A',
                        'color' => $customer->assetdetailsfinnone->assetdetails->color ?? 'N/A'
                    ];
                });
        }

        // ----------------------------------
        // SIGNATORIES
        // ----------------------------------
        $selectedSignatories = Signatory::pluck("SIGNATORY")->toArray();

        return inertia('main-modules/cfp/certificate', [
            'selectedCustomers' => $selectedCustomer,
            'selectedSignatory' => $selectedSignatories

        ]);
    }


    public function generateJasperPdf(Request $request, JasperServerService $jasperServerService)
    {
        $reportPath = '/Document_report/CFP_NEW';

        $params = [
            'loan' => $request->input('loan') ?? '',
            'fullname' => $request->input('fullname') ?? '',
            'brand' => $request->input('brand') ?? '',
            'model' => $request->input('model') ?? '',
            'chassis' => $request->input('chassis') ?? '',
            'engineno' => $request->input('engineno') ?? '',
            'color' => $request->input('color') ?? '',
            'signatory' => $request->input('signatory') ?? '',
            'position' => $request->input('position') ?? '',
            'currentmonth' => $request->input('currentmonth', now()->format('F')) ?? '',
            'currentyear' => $request->input('currentyear', now()->format('Y')) ?? '',
            'daysuffix' => $request->input('daysuffix', now()->format('jS')) ?? '',
            'branch' => $request->input('branch') ?? '',
        ];

        Log::channel('cfp')->info('Successfully Generated CFP', ['agreement' => $params['loan']]);

        $this->auditLogs('GENERATED CFP OF ' . $params['loan'], $this->module);

        return $jasperServerService->generatePdfReport($reportPath, $params, 'cfp');
    }

    public function streamPdf(Request $request)
    {

        $filename = $request->input('filename');
        $path = storage_path("app/reports/cfp/output/{$filename}");
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->stream(function () use ($path) {
            readfile($path);
            if (file_exists($path)) {
                unlink($path);
            }
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    public function getCfpSignatory(Request $request)
    {
        $signatory_value = $request->signatory_value;

        if ($signatory_value) {
            $selectedSignatories = Signatory::where('SIGNATORY', $signatory_value)
                ->get()
                ->map(function ($signatory) {
                    return [
                        'branch' => $signatory->BRANCH,
                        'signatory' => $signatory->SIGNATORY,
                        'code' => $signatory->CODE,
                        'sss_id' => $signatory->SSS_ID,
                        'tin_id' => $signatory->TIN_ID,
                        'position' => $signatory->POSITION
                    ];
                });
        }

        return response()->json($selectedSignatories);
    }
}
