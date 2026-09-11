<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;

class AdminRoleFilter
{
    public function apply(Builder $query, $model): void
    {
        if ($model === "App\Models\Role") {
            $query->whereNotIn('name', ['Admin', 'SuperAdmin'])
            ->withCount('users');
        }
    }
}
