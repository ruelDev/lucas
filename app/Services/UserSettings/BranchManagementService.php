<?php

namespace App\Services\UserSettings;

use App\Models\Branch;

class BranchManagementService
{
    public function bmQuery()
    {
        return Branch::get()->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'branch_code' => $branch->branch_code,
                'location' => $branch->location,
                'status' => $branch->status,
                'remarks' => $branch->remarks,
                'created_at' => $branch->created_at,
                'updated_at' => $branch->updated_at
            ];
        });
    }
}
