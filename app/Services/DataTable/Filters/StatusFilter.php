<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StatusFilter
{
    public function apply(Builder $query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }
}
