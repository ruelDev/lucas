<?php

namespace App\Traits;

use App\Services\DataTableService;
use Illuminate\Http\Request;

trait HasDataTable
{
    protected function getDataTableService(): DataTableService
    {
        return new DataTableService();
    }

    /**
     * Handle DataTable request with configuration array
     *
     * @param Request $request
     * @param mixed $model
     * @param array $config Configuration options:
     *  - searchableColumns: array (default: [])
     *  - allowedSortColumns: array (default: [])
     *  - selectColumns: array (default: ['*'])
     *  - defaultSortColumn: string (default: 'id')
     *  - defaultSortDirection: string (default: 'desc')
     *  - relationshipColumns: array (default: [])
     *  - eagerLoad: array (default: [])
     *  - pivotColumns: array (default: [])
     *  - isShowColumns: bool (default: false)
     *  - isUserFiltered: bool (default: false)
     *  - userFilterColumn: string (default: 'id')
     * @return array
     */
    protected function handleDataTableRequest(
        Request $request,
        $model,
        array $config = []
    ): array {
        $defaults = [
            'searchableColumns' => [],
            'allowedSortColumns' => [],
            'selectColumns' => ['*'],
            'defaultSortColumn' => 'id',
            'defaultSortDirection' => 'desc',
            'relationshipColumns' => [],
            'eagerLoad' => [],
            'pivotColumns' => [],
            'isShowColumns' => false,
            'isUserFiltered' => false,
            'userFilterColumn' => 'id',
        ];

        $config = array_merge($defaults, $config);

        return $this->getDataTableService()
            ->setModel($model)
            ->setSearchableColumns($config['searchableColumns'])
            ->setAllowedSortColumns($config['allowedSortColumns'])
            ->setSelectColumns($config['selectColumns'])
            ->setDefaultSort($config['defaultSortColumn'], $config['defaultSortDirection'])
            ->setRelationshipColumns($config['relationshipColumns'])
            ->setEagerLoad($config['eagerLoad'])
            ->setPivotColumns($config['pivotColumns'])
            ->setIsShowColumns($config['isShowColumns'])
            ->setIsUserFiltered($config['isUserFiltered'])
            ->setUserFilterColumn($config['userFilterColumn'])
            ->getData($request);
    }
}
