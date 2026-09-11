<?php

namespace Database\Seeders;

use Database\Seeders\OutCollectionPermissions\DailyCollectionSeeder;
use Database\Seeders\OutCollectionPermissions\LmsPostingSeeder;
use Database\Seeders\OutCollectionPermissions\OutCollectionSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\versions\v1_0_0_Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            v1_0_0_Seeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            CfpPermissionSeeder::class,
            ReceiptConverterPermissionSeeder::class,
            DealerSeeder::class,
            StatusSeeder::class,

            // Head Office
            HeadOfficeGroupSeeder::class,
            HeadOfficeDivisionSeeder::class,
            HeadOfficeDepartmentSeeder::class,
            HeadOfficeSectionSeeder::class,

            // Bank
            BankSeeder::class,
            BankPermissionSeeder::class,
            BankAccountSeeder::class,

            // Out Collection Report
            OutCollectionPermissionSeeder::class,
            DailyCollectionSeeder::class,
            OutCollectionSeeder::class,
            LmsPostingSeeder::class,

            // OSF
            OfflineSearchFacilityPermissionSeeder::class,
        ]);
    }
}
