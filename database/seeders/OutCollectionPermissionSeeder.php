<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use App\Models\Role;

class OutCollectionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = [
            'maker' => [
                "out_collection.view",
                "out_collection.create",
                "out_collection.edit",
                "out_collection.delete",
            ],
            'author' => [
                "out_collection.view",
                "out_collection.edit",
                "out_collection.authorize",
            ],
        ];

        $allPermissions = collect($permissionGroup)->flatten();

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        /**
         * Update Admin Role
         */
        $adminRole = Role::where('name', 'Admin')->first();

        $allPermissions = Permission::pluck("name")
            ->reject(fn($permission) => $permission === 'out_collection.create')
            ->toArray();

        $adminRole->syncPermissions($allPermissions);

        $outCollectionRoles = [
            'Out Collection - Maker' => [
                'groups' => [
                    'maker'
                ],
                'description' => 'Out Collection User for encoding out collections.'
            ],
            'Out Collection - Author' => [
                'groups' => [
                    'author'
                ],
                'description' => 'Out Collection User for authorizing collections encoded by the Maker.'
            ]
        ];

        foreach ($outCollectionRoles as $roleName => $roleData) {
            $permissions = collect($roleData['groups'])
                ->flatMap(fn($group) => $permissionGroup[$group])
                ->unique()
                ->values()
                ->toArray();

            $role = Role::firstOrCreate([
                'name' => $roleName,
                'description' => $roleData['description'],
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }
    }
}
