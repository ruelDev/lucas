<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class OfflineSearchFacilityPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'offline_search_facility.view',
        ];

        foreach ($permissions as $value) {
            Permission::create([
                "name" => $value,
                'guard_name' => 'web'
            ]);
        }

        /**
         * Update Admin Role
         */
        $adminRole = Role::where('name', 'Admin')->first();

        $allPermissions = Permission::pluck("name")->toArray();

        $adminRole->syncPermissions($allPermissions);

        /**
         * Create Role for Offline Search Facility
         */
        $offlineSearchFacilityRole = Role::firstOrCreate([
            'name' => 'Offline Search Facility',
            'description' => 'Role with special access to Offline Search Facility',
            'guard_name' => 'web'
        ]);

        $offlineSearchFacilityRole->syncPermissions($permissions);
    }
}
