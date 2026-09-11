<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class BankPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            "bank_management.view",
            "bank_management.create",
            "bank_management.update",
            "bank_management.delete",
        ];

        foreach ($permissions as $value) {
            Permission::create([
                "name" => $value,
                'guard_name' => 'web'
            ]);
        }

        $adminRole = Role::where('name', 'Admin')->first();

        $allPermissions = Permission::pluck("name")->toArray();

        $adminRole->syncPermissions($allPermissions);
    }
}
