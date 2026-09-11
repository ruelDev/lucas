<?php

namespace App\Services\DataTable;

use App\Services\DataTable\Filters\AdminAuditLogFilter;
use App\Services\DataTable\Filters\AdminRoleFilter;
use App\Services\DataTable\Filters\AreaFilter;
use App\Services\DataTable\Filters\SearchFilter;
use App\Services\DataTable\Filters\StandardFilter;
use App\Services\DataTable\Filters\DateFilter;
use App\Services\DataTable\Filters\ModuleFilter;
use App\Services\DataTable\Filters\RoleFilter;
use App\Services\DataTable\Filters\UserFilter;
use App\Services\DataTable\Filters\SortFilter;
use App\Services\DataTable\Filters\StatusFilter;
use Illuminate\Http\Request;

class DataTableQueryBuilder
{
    protected DataTableConfig $config;
    protected SearchFilter $searchFilter;
    protected StatusFilter $statusFilter;
    protected StandardFilter $standardFilter;
    protected DateFilter $dateFilter;
    protected UserFilter $userFilter;
    protected SortFilter $sortFilter;
    protected AdminRoleFilter $adminRoleFilter;
    protected AdminAuditLogFilter $adminAuditLogFilter;
    protected RoleFilter $roleFilter;
    protected AreaFilter $areaFilter;
    protected ModuleFilter $moduleFilter;

    public function __construct(DataTableConfig $config)
    {
        $this->config = $config;
        $this->searchFilter = new SearchFilter($config);
        $this->statusFilter = new StatusFilter($config);
        $this->standardFilter = new StandardFilter();
        $this->dateFilter = new DateFilter();
        $this->userFilter = new UserFilter($config);
        $this->sortFilter = new SortFilter($config);
        $this->adminRoleFilter = new AdminRoleFilter();
        $this->adminAuditLogFilter = new AdminAuditLogFilter();
        $this->roleFilter = new RoleFilter();
        $this->areaFilter = new AreaFilter();
        $this->moduleFilter = new ModuleFilter();
    }

    public function build(Request $request): array
    {
        $model = $this->config->model;

        $query = $model::query();

        if (!empty($this->config->eagerLoad)) {
            $query->with($this->config->eagerLoad);
        }

        $this->adminRoleFilter->apply($query, $model);
        $this->adminAuditLogFilter->apply($query, $model);
        $this->searchFilter->apply($query, $request);
        $this->statusFilter->apply($query, $request);
        $this->standardFilter->apply($query, $request);
        $this->dateFilter->apply($query, $request);
        $this->userFilter->apply($query, $request);
        $this->sortFilter->apply($query, $request);
        $this->roleFilter->apply($query, $request);
        $this->areaFilter->apply($query, $request);
        $this->moduleFilter->apply($query, $model, $request);

        if (!empty($this->config->selectColumns)) {
            $query->select($this->config->selectColumns);
        }

        $perPage = min(
            $request->get('per_page', $this->config->defaultPerPage),
            $this->config->maxPerPage
        );

        $data = $query->paginate($perPage);

        if (!$request->filled('search') && !$this->config->isShowColumns) {
            return $this->buildEmptyResponse($request, $perPage);
        }

        return $this->buildResponse($data, $request);
    }

    protected function buildEmptyResponse(Request $request, int $perPage): array
    {
        return [
            'data' => [],
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'from' => null,
                'to' => null
            ],
            'filters' => $request->only(['search', 'filters', 'date_from', 'date_to', 'module']),
            'sort' => [
                'column' => $request->get('sort', $this->config->defaultSortColumn),
                'direction' => $request->get('direction', $this->config->defaultSortDirection)
            ]
        ];
    }

    protected function buildResponse($data, Request $request): array
    {
        return [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem()
            ],
            'filters' => $request->only(['search', 'filters', 'status', 'date_from', 'date_to', 'module']),
            'sort' => [
                'column' => $request->get('sort', $this->config->defaultSortColumn),
                'direction' => $request->get('direction', $this->config->defaultSortDirection)
            ]
        ];
    }
}
