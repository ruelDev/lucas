<?php

namespace Database\Seeders\OutCollectionPermissions;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DailyCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            "daily_collection_report.view",
            "daily_collection_report.export"
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
