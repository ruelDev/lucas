<?php

namespace Database\Seeders\OutCollectionPermissions;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class OutCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            "out_collection_report.view",
            "out_collection_report.export"
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
