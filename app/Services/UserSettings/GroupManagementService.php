<?php

namespace App\Services\UserSettings;

use App\Models\Group;

class GroupManagementService
{
    public function gmQuery()
    {
        return Group::get()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'status' => $group->status,
                'remarks' => $group->remarks,
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at
            ];
        });
    }
}
