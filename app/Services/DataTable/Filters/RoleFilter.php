<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RoleFilter
{
    public function apply(Builder $query, Request $request): void
    {
        if ($request->filled('role')) {
            $query->role($request->role);
        }
    }
}
