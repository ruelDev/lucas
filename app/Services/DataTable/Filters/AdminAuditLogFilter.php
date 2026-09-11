<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdminAuditLogFilter
{
    public function apply(Builder $query, $model): void
    {
        if ($model === "App\Models\AuditLog") {
            $user = Auth::user();

            if (!($user->hasRole('Admin')) && !($user->can('audit_logs.adminview'))) {
                $query->where('user_id', $user->id);
            }
        }
    }
}
