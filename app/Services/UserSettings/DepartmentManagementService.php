<?php

namespace App\Services\UserSettings;

use App\Models\Department;

class DepartmentManagementService
{
    public function deptQuery()
    {
        return Department::get()->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'description' => $department->description,
                'group' => $department->group_id,
                'division' => $department->division_id,
                'group_name' => $department->group->name,
                'division_name' => $department->division->name ?? null,
                'status' => $department->status,
                'remarks' =>  $department->remarks,
                'created_at' => $department->created_at,
                'updated_at' => $department->updated_at
            ];
        });
    }
}
