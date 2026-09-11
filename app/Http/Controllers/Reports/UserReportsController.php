<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSettings\HeadOfficeController;
use App\Models\User;
use App\Services\JasperServerService;
use App\Services\OfficesService;
use App\Services\UserSettings\UserManagementExportService;
use App\Services\UserSettings\UserManagementService;
use App\Services\UserSettings\UserManagementSyncPreparationService;
use App\Traits\HasAuditLog;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserReportsController extends Controller
{
    use HasDataTable, HasAuditLog;

    protected $module = 'USER REPORTS';

    protected $userManagementService;
    protected $userManagementExportService;
    protected $userManagementSyncPreparationService;
    protected $areaFetchingController;
    protected $headOfficeService;

    public function __construct(
        UserManagementService $userManagementService,
        UserManagementExportService $userManagementExportService,
        UserManagementSyncPreparationService $userManagementSyncPreparationService,
        HeadOfficeController $areaFetchingController,
        OfficesService $headOfficeService
    ) {
        $this->userManagementService = $userManagementService;
        $this->userManagementExportService = $userManagementExportService;
        $this->userManagementSyncPreparationService = $userManagementSyncPreparationService;
        $this->areaFetchingController = $areaFetchingController;
        $this->headOfficeService = $headOfficeService;
    }

    public function index(Request $request)
    {
        Log::channel('user_management')->info('Accessed the User Reports Page', ['user_id' => Auth::id()]);

        $filterKeys = ['area', 'role', 'date_from', 'date_to'];

        $filledCount = collect($filterKeys)
            ->filter(fn($key) => $request->filled($key))
            ->count();

        $isFilterSet = $filledCount >= 2;

        $results = $this->handleDataTableRequest(
            $request,
            User::class,
            [
                'searchableColumns' => [['fname', 'mname', 'lname'], 'employee_id', 'position', 'email', 'company', 'created_at'],
                'allowedSortColumns' => ['employee_id', 'fname', 'position', 'status', 'updated_at'],
                'selectColumns' => ['id', 'fname', 'mname', 'lname', 'employee_id', 'email', 'company', 'position', 'isBranchDealer', 'status', 'remarks', 'updated_at'],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'eagerLoad' => [
                    'roles',
                    'branchDealerUser.branch',
                    'branchDealerUser.dealer',
                    'headOfficeUser.group',
                    'headOfficeUser.division',
                    'headOfficeUser.department',
                    'headOfficeUser.section',
                ],
                'isShowColumns' => $isFilterSet
            ]
        );


        $results['data'] = collect($results['data'])->map(function ($item) {

            $formatDate = fn($date) => $date?->format('Y-m-d H:i:s');

            $name = $item->fname . " " . ($item->mname ?? '') . " " . $item->lname;

            $areaRaw = $this->userManagementService
                ->prepareHeadOfficeUserOrBranchDealerUser($item);

            $area = trim(preg_replace(
                '/\s+/',
                ' ',
                strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $areaRaw))
            ));

            $role = $item->roles->first()?->name;

            $session = $this->userManagementExportService->getLastSession($item->id);

            return [
                'employee_id' => $item->employee_id,
                'name' => $name,
                'role' => $role,
                'area' => $area,
                'date_created' => $formatDate($item->created_at),
                'last_login_date' => $formatDate($session['last_login']),
                'last_logout_date' => $formatDate($session['last_logout']),
                'last_password_change' => $item->password_changed_at,
                'account_status' => $item->status,
                'remarks' => $item->remarks,
                'account_expiration_date' => $item->expiration_date,
            ];
        })->toArray();

        foreach ($filterKeys as $key) {
            if ($request->filled($key)) {
                $results['filters'][$key] = $request->input($key);
            }
        }

        return inertia('reports/user-reports/index', $results);
    }

    public function getAreaOptions()
    {
        return response()->json([
            'data' => $this->headOfficeService->getAllAreaOptions()
        ]);
    }

    public function generateJasperReport(Request $request, JasperServerService $jasperServerService)
    {
        $reportPath = '/Document_report/User_Reports/user_report';

        $params = [
            'role' => $request->input('role'),
            'area' => $request->input('area'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $this->auditLogs('GENERATED USER REPORT', $this->module);

        return $jasperServerService->generateCsvReport($reportPath, $params, 'user-report');
    }
}
