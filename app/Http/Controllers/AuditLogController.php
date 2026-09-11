<?php

namespace App\Http\Controllers;

use App\Exports\AuditExport;
use App\Models\AuditLog;
use App\Services\AuditLogManagementExportService;
use App\Services\OfficesService;
use App\Traits\HasDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditLogController extends Controller
{
    use HasDataTable;

    protected $officesService;

    public function __construct(OfficesService $officesService) {
        $this->officesService = $officesService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('audit_logs')->info('Accessed the Audit Logs Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            AuditLog::class,
            [
                'searchableColumns' => ['module', 'event', 'ip_address', 'created_at'],
                'allowedSortColumns' => ['module', 'event', 'ip_address', 'updated_at', 'user.fname', 'user.mname', 'user.lname', 'user.employee_id'],
                'selectColumns' => ['id', 'user_id', 'module', 'event', 'old_data', 'new_data', 'model', 'ip_address', 'created_at',],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'relationshipColumns' => ['user' => [['fname', 'mname', 'lname'], 'employee_id']],
                'eagerLoad' => ['user', 'user.roles'],
                'isShowColumns' => true
            ]
        );

        $results['data'] = collect($results['data'])->map(function ($item) {

            $mname = $item->user?->mname ? $item->user->mname . " " : '';
            $name = $item->user
                ? $item->user->fname . " " . $mname . $item->user->lname
                : '(deleted user)';
            $employee_id = $item->user?->employee_id;

            $selectEvent = [
                'LOGGED IN',
                'LOGGED OUT',
            ];

            $notIdPrefixes = [
                'GENERATED',
                'AUTHORIZED',
                'SENT BACK',
                'SENT TO AUTHOR'
            ];

            $oldData = json_decode($item->old_data, true);
            $newData = json_decode($item->new_data, true);

            // Authentication-module events (SSO Login, SSO Logout, etc.) and session
            // events don't reference a specific record, so skip the ID suffix.
            $isSelectEvent = in_array($item->event, $selectEvent)
                || in_array($item->module, ['Authentication', 'Session'])
                || Str::startsWith($item->event, $notIdPrefixes);

            $affectedData = $isSelectEvent
                ? null
                : ($oldData['id'] ?? $newData['id'] ?? null);

            $model = $item->model;

            if ($isSelectEvent) {
                $event = $item->event;
            } else {
                $event = "{$item->event} {$model}: ID - {$affectedData}";
            }

            $action = "{$event}";

            $role = $item->user->roles->pluck('name')
                ->filter(fn ($r) => !in_array($r, ['sso_admin', 'sso_user']))
                ->first();

            return [
                'id' => $item->id,
                'created_at' => $item->created_at,
                'name' => $name,
                'position' => $item->user->position,
                'role' => $role,
                'employee_id' => $employee_id,
                'module' => $item->module,
                'action' => $action,
                'ip_address' => $item->ip_address,
            ];
        })->toArray();

        return inertia('audit_logs/index', $results);
    }


    public function export(Request $request)
    {
        $searchVal = $request->input('searchVal') ?? '';
        $sortBy = $request->input('sortBy') ?? 'created_at';
        $sortDir = $request->input('sortDir') ? 'DESC' : 'ASC';
        $exportType = $request->input('exportType');
        $dateFrom = $request->input('dateFrom') ?? '';
        $dateTo = $request->input('dateTo') ?? '';
        $moduleSelect = $request->input('moduleSelect') ?? '';

        $auditLogExport = new AuditLogManagementExportService;

        $auditLogs = json_decode(json_encode($auditLogExport->auditLogExportQuery($searchVal, $sortBy, $sortDir, $dateFrom, $dateTo, $moduleSelect)));

        Log::channel('audit_logs')->info('Export Results',['logs' => $auditLogs, 'searchVal' => $searchVal, 'sortBy' => $sortBy, 'sortDir' => $sortDir, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);

        Log::channel('audit_logs')->info('Exported Audit Logs', ['user_id' => Auth::id()]);

        if ($exportType === 'pdf') {
            $pdf = Pdf::loadView('exports.audit_export', [
                'auditLogs' => $auditLogs,
                'searchVal' => $searchVal,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
            ])
                ->setPaper('letter', 'landscape')
                ->setOptions(['defaultFont' => 'helvetica']);

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="auditLogs.pdf');
        } elseif ($exportType === 'csv' || $exportType === 'xlsx') {
            return Excel::download(new AuditExport($exportType, $searchVal, $sortBy, $sortDir, $dateFrom, $dateTo, $moduleSelect), 'logs.' . $exportType);
        }
    }

    public function getModuleOptions()
    {
        return response()->json($this->officesService->getModuleOptions());
    }
}
