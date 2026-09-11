<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLog
{
    public function auditLogs(string $action, string $module, ?Model $model = null)
    {
        return AuditLog::create([
            'user_id' => Auth::user()->id,
            'event' => $action,
            'model' => $model ? get_class($model) . ' : ' . $model->getKey() : null,
            'module' => $module,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
