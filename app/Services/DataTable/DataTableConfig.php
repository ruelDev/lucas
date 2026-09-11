<?php

namespace App\Services\DataTable;

class DataTableConfig
{
    protected $model;
    protected array $searchableColumns = [];
    protected array $allowedSortColumns = [];
    protected array $relationshipColumns = [];
    protected array $eagerLoad = [];
    protected string $defaultSortColumn = 'id';
    protected string $defaultSortDirection = 'desc';
    protected array $selectColumns = ['*'];
    protected int $defaultPerPage = 10;
    protected int $maxPerPage = 100;
    protected array $pivotColumns = [];
    protected bool $isShowColumns = false;
    protected bool $isUserFiltered = false;
    protected string $userFilterColumn = 'id';

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    // Use magic methods to reduce method count
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException("Property {$name} does not exist");
    }

    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        }

        throw new \InvalidArgumentException("Property {$name} does not exist");
    }

    // Keep only essential fluent setters for the public API
    public function setModel($model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setSearchableColumns(array $columns): self
    {
        $this->searchableColumns = $columns;
        return $this;
    }

    public function setAllowedSortColumns(array $columns): self
    {
        $this->allowedSortColumns = $columns;
        return $this;
    }

    public function setRelationshipColumns(array $columns): self
    {
        $this->relationshipColumns = $columns;
        return $this;
    }

    public function setPivotColumns(array $pivotColumns): self
    {
        $this->pivotColumns = $pivotColumns;
        return $this;
    }

    public function setIsShowColumns(bool $isShowColumns): self
    {
        $this->isShowColumns = $isShowColumns;
        return $this;
    }

    public function setIsUserFiltered(bool $isUserFiltered): self
    {
        $this->isUserFiltered = $isUserFiltered;
        return $this;
    }

    public function setUserFilterColumn(string $userFilterColumn): self
    {
        $this->userFilterColumn = $userFilterColumn;
        return $this;
    }

    public function setEagerLoad(array $relationships): self
    {
        $this->eagerLoad = $relationships;
        return $this;
    }

    public function setDefaultSort(string $column, string $direction = 'desc'): self
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = $direction;
        return $this;
    }

    public function setSelectColumns(array $columns): self
    {
        $this->selectColumns = $columns;
        return $this;
    }

    public function setDefaultPerPage(int $perPage): self
    {
        $this->defaultPerPage = $perPage;
        return $this;
    }

    public function setMaxPerPage(int $maxPerPage): self
    {
        $this->maxPerPage = $maxPerPage;
        return $this;
    }
}
