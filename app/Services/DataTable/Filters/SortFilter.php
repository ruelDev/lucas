<?php

namespace App\Services\DataTable\Filters;

use App\Services\DataTable\DataTableConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SortFilter
{
    protected DataTableConfig $config;

    public function __construct(DataTableConfig $config)
    {
        $this->config = $config;
    }

    public function apply(Builder $query, Request $request): void
    {
        $sortColumn = $request->get('sort', $this->config->defaultSortColumn);
        $sortDirection = $request->get('direction', $this->config->defaultSortDirection);

        if (in_array($sortColumn, $this->config->allowedSortColumns)) {
            if (str_contains($sortColumn, '.')) {
                $this->applyRelationshipSort($query, $sortColumn, $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $query->orderBy(
                $this->config->defaultSortColumn,
                $this->config->defaultSortDirection
            );
        }
    }

    protected function applyRelationshipSort(Builder $query, string $sortColumn, string $sortDirection): void
    {
        [$relationPath, $column] = explode('.', $sortColumn, 2);

        $model = $this->config->model;
        $relationInstance = $model::query()->getModel();
        $relations = explode('.', $relationPath);
        $relationName = array_shift($relations);

        $relation = $relationInstance->$relationName();
        $relatedTable = $relationInstance->getRelated()->getTable();
        $foreignKey = $relation->getQualifiedForeignKeyName();
        $ownerKey = $relationInstance->getQualifiedOwnerKeyName();

        $query->leftJoin($relatedTable, $foreignKey, '=', $ownerKey)
            ->orderBy("{$relatedTable}.{$column}", $sortDirection)
            ->select($model::query()->getModel()->getTable() . '.*');
    }
}
