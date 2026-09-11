<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Draft',
            'Sent Back',
            'Sent to Author',
            'Authorized'
        ];

        foreach ($statuses as $status) {
            Status::create(['name' => $status]);
        }
    }
}
