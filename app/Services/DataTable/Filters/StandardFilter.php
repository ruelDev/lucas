<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StandardFilter
{
    public function apply(Builder $query, Request $request): void
    {
        if (!$request->filled('filters')) {
            return;
        }

        foreach ($request->filters as $filterKey => $value) {
            if (str_contains($filterKey, '.')) {
                $this->applyRelationshipFilter($query, $filterKey, $value);
            } else {
                $query->where($filterKey, 'like', "%{$value}%");
            }
        }
    }

    protected function applyRelationshipFilter(Builder $query, string $filterKey, $value): void
    {
        [$relation, $column] = explode('.', $filterKey, 2);

        $query->whereHas($relation, function ($relationQuery) use ($column, $value) {
            $relationQuery->where($column, 'like', "%$value%");
        });
    }
}
