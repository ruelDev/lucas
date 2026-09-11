<?php

namespace App\Services;

use App\Services\DataTable\DataTableQueryBuilder;
use App\Services\DataTable\DataTableConfig;
use Illuminate\Http\Request;

class DataTableService
{
    protected DataTableConfig $config;
    protected DataTableQueryBuilder $queryBuilder;

    public function __construct($model = null)
    {
        $this->config = new DataTableConfig($model);
        $this->queryBuilder = new DataTableQueryBuilder($this->config);
    }

    public function setModel($model): self
    {
        $this->config->setModel($model);
        return $this;
    }

    public function setSearchableColumns(array $columns): self
    {
        $this->config->setSearchableColumns($columns);
        return $this;
    }

    public function setAllowedSortColumns(array $columns): self
    {
        $this->config->setAllowedSortColumns($columns);
        return $this;
    }

    public function setRelationshipColumns(array $columns): self
    {
        $this->config->setRelationshipColumns($columns);
        return $this;
    }

    public function setPivotColumns(array $pivotColumns): self
    {
        $this->config->setPivotColumns($pivotColumns);
        return $this;
    }

    public function setIsShowColumns(bool $isShowColumns): self
    {
        $this->config->setIsShowColumns($isShowColumns);
        return $this;
    }

    public function setIsUserFiltered(bool $isUserFiltered): self
    {
        $this->config->setIsUserFiltered($isUserFiltered);
        return $this;
    }

    public function setUserFilterColumn(string $userFilterColumn): self
    {
        $this->config->setUserFilterColumn($userFilterColumn);
        return $this;
    }

    public function setEagerLoad(array $relationships): self
    {
        $this->config->setEagerLoad($relationships);
        return $this;
    }

    public function setDefaultSort(string $column, string $direction = 'desc'): self
    {
        $this->config->setDefaultSort($column, $direction);
        return $this;
    }

    public function setSelectColumns(array $columns): self
    {
        $this->config->setSelectColumns($columns);
        return $this;
    }

    public function setDefaultPerPage(int $perPage): self
    {
        $this->config->setDefaultPerPage($perPage);
        return $this;
    }

    public function setMaxPerPage(int $maxPerPage): self
    {
        $this->config->setMaxPerPage($maxPerPage);
        return $this;
    }

    public function getData(Request $request): array
    {
        return $this->queryBuilder->build($request);
    }

    public function applyCustomQuery($query)
    {
        return $query;
    }
}
