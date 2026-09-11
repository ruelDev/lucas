<?php

namespace App\Services\DataTable\Filters;

use App\Services\DataTable\DataTableConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchFilter
{
    protected DataTableConfig $config;

    public function __construct(DataTableConfig $config)
    {
        $this->config = $config;
    }

    public function apply(Builder $query, Request $request): void
    {
        if (!$this->shouldApply($request)) {
            return;
        }

        $searchTerm = $request->search;

        $query->where(function ($q) use ($searchTerm) {
            $this->searchDirectColumns($q, $searchTerm);
            $this->searchRelationshipColumns($q, $searchTerm);
            $this->searchPivotColumns($q, $searchTerm);
        });
    }

    protected function shouldApply(Request $request): bool
    {
        return $request->query('search')
            && (!isset($this->config->searchableColumns)
                || !isset($this->config->relationshipColumns)
                || !isset($this->config->pivotColumns));
    }

    protected function searchDirectColumns(Builder $query, string $searchTerm): void
    {
        foreach ($this->config->searchableColumns as $column) {
            if (is_array($column)) {
                $this->applyConcatMatch($query, $column, $searchTerm);
                continue;
            }
            $query->orWhere($column, 'like', "%{$searchTerm}%");
        }
    }

    protected function searchRelationshipColumns(Builder $query, string $searchTerm): void
    {
        foreach ($this->config->relationshipColumns as $relationColumn => $fields) {
            $query->orWhereHas($relationColumn, function ($relationQuery) use ($fields, $searchTerm) {
                $relationQuery->where(function ($subQuery) use ($fields, $searchTerm) {
                    foreach ($fields as $field) {
                        if (is_array($field)) {
                            $this->applyConcatMatch($subQuery, $field, $searchTerm);
                            continue;
                        }
                        $subQuery->orWhere($field, 'like', "%{$searchTerm}%");
                    }
                });
            });
        }
    }

    protected function searchPivotColumns(Builder $query, string $searchTerm): void
    {
        foreach ($this->config->pivotColumns as $relation => $pivotFields) {
            $query->orWhereHas($relation, function ($relationQuery) use ($pivotFields, $searchTerm) {
                foreach ($pivotFields as $pivotField) {
                    $relationQuery->orWherePivot($pivotField, 'like', "%{$searchTerm}%");
                }
            });
        }
    }

    protected function applyConcatMatch(Builder $query, array $columns, string $searchTerm): void
    {
        $concatExpression = "CONCAT_WS(' ', " . implode(', ', $columns) . ")";
        $terms = preg_split('/\s+/', trim($searchTerm));

        $query->orWhere(function ($q) use ($concatExpression, $terms) {
            foreach ($terms as $term) {
                $q->whereRaw("{$concatExpression} LIKE ?", ['%' . $term . '%']);
            }
        });
    }
}
