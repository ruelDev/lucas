<?php

namespace App\Services\UserSettings;

use App\Models\Section;

class SectionManagementService
{
    public function sectionQuery()
    {
        return Section::get()->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'group' => $section->group_id,
                'division' => $section->division_id,
                'department' => $section->department_id ?? null,
                'group_name' => $section->group->name,
                'division_name' => $section->division->name,
                'department_name' => $section->department->name ?? null,
                'status' => $section->status,
                'remarks' =>  $section->remarks,
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at
            ];
        });
    }
}
