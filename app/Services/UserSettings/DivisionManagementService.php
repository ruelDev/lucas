<?php

namespace App\Services\UserSettings;

use App\Models\Division;

class DivisionManagementService
{
    public function divQuery()
    {
        return Division::with('group')->get()->map(function ($division) {
            return [
                'id' => $division->id,
                'name' => $division->name,
                'group' => $division->group_id,
                'group_name' => $division->group->name,
                'description' => $division->description,
                'status' => $division->status,
                'remarks' =>  $division->remarks,
                'created_at' => $division->created_at,
                'updated_at' => $division->updated_at
            ];
        });
    }
}
