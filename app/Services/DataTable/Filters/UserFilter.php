<?php

namespace App\Services\DataTable\Filters;

use App\Models\User;
use App\Services\DataTable\DataTableConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserFilter
{
    protected DataTableConfig $config;

    public function __construct(DataTableConfig $config)
    {
        $this->config = $config;
    }

    public function apply(Builder $query): void
    {
        $sessionUser = Auth::user();

        if (!$sessionUser) {
            return;
        }

        $employeeId = $sessionUser->employee_id;

        if (!$employeeId || !$this->config->isUserFiltered || !$this->config->userFilterColumn) {
            return;
        }

        if ($sessionUser->can('out_collection.authorize')) {
            $query->whereNot('status', 'Draft');
        } else {
            $query->where($this->config->userFilterColumn, $employeeId);
        }
    }
}
