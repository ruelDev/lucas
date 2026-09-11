<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ModuleFilter
{
    public function apply(Builder $query, $model, Request $request): void
    {
        if ($request->filled('module') && $model === "App\Models\AuditLog") {
            $query->where('module', $request->module);
        }
    }
}
