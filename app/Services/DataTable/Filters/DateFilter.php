<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DateFilter
{
    public function apply(Builder $query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }
}
