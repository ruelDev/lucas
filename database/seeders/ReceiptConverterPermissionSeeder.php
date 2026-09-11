<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ReceiptConverterPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'receipt_converter.view',
            'receipt_converter.validate',
            'receipt_converter.export'
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
         * Create Role for Receipt Converter
         */
        $receiptConverterRole = Role::firstOrCreate([
            'name' => 'Receipt Converter',
            'description' => 'Role with special access to Receipt Converter Report Generation',
            'guard_name' => 'web'
        ]);

        $receiptConverterRole->syncPermissions($permissions);
    }
}
