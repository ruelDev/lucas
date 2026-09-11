<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogManagementService
{
    public function auditLogQuery()
    {
        return AuditLog::query()->with('user')->latest()->get()->map(function ($audit) {
            $name = $audit->user->fname . " " . $audit->user->mname . " " . $audit->user->lname;

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
                'created_at' => $audit->created_at,
            ];
        });
    }
}
