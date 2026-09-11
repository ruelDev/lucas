<?php

namespace Database\Seeders\OutCollectionPermissions;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LmsPostingSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            "lms_posting_report.view",
            "lms_posting_report.export"
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
