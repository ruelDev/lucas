<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class CfpPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            "certificate_fullpayment.view",
            "certificate_fullpayment.export",
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
         * Create Role fpr CFP
         */
        $cfpRole = Role::firstOrCreate([
            'name' => 'Certificate of Full Payment',
            'description' => 'Role with access to CFP certificate generation.',
            'guard_name' => 'web'
        ]);

        $cfpRole->syncPermissions($permissions);
    }
}
