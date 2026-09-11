<?php

namespace App\Services\Dashboard\Components;

use App\Models\Role;

class RolesCharts
{
    public function prepareRoleChartsData()
    {
        return Role::with('permissions', 'users')
            ->whereNotIn('name', ['Admin', 'Super-Admin', 'Super-User'])
            ->get()
            ->map(fn($role) => [
                "name" => $role->name,
                "created_at" => $role->created_at->format("m/d/y"),
                "permissions" => $role->permissions->count(),
                "users" => $role->users->count(),
            ]);
    }

}
