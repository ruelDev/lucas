<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogManagementExportService
{
    /**
     * Create a new class instance.
     */
    public function auditLogExportQuery(string $searchVal, string $sortBy, string $sortDir, string $dateFrom, string $dateTo, string $moduleSelect)
    {
        switch ($sortBy) {
            case 'name':
                $sort = 'fname';
                break;
            case 'action':
                $sort = 'event';
                break;
            default:
                $sort = $sortBy;
        }

        $auditLogQuery = AuditLog::query()
            ->with('user')
            ->when(!empty($dateFrom), function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when(!empty($dateTo), function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            })
            ->when(!empty($moduleSelect), function ($query) use ($moduleSelect) {
                $query->where('module', $moduleSelect);
            })
            ->search($searchVal)
            ->orderBy($sort, $sortDir)
            ->get();

        return $auditLogQuery->map(function ($audit) {
            $name = $audit->user->fname . " " . (($audit->user->mname ?? "") . " ") . $audit->user->lname;

            $oldData = json_decode($audit->old_data, true);
            $newData = json_decode($audit->new_data, true);

            $isSelectEvent = in_array($audit->event, ['LOGGED IN', 'LOGGED OUT'])
                || in_array($audit->module, ['Authentication', 'Session']);

            $affectedData = $isSelectEvent ? null : ($oldData['id'] ?? $newData['id'] ?? null);

            $model = $audit->model;

            if ($isSelectEvent) {
                $event = $audit->event;
                $action = "{$event}";
            } else {
                $event = "{$audit->event} {$model}: ID - {$affectedData}";
                $action = "{$event}";
            }

            return [
                'employee_id' => $audit->user->employee_id,
                'timestamp' => date('F, d Y, H:i:s', strtotime($audit->created_at)),
                'name' => $name,
                'module' => $audit->module,
                'action' => $action,
                'ip_address' => $audit->ip_address,
            ];
        });
    }
}
