<?php

namespace Database\Seeders\versions;

use App\Models\AppVersion;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class v1_0_2_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $version = AppVersion::firstOrCreate([
            'version' => '1.0.2',
            'release_type' => 'PATCH',
            'released_at' => Carbon::now(),
        ]);

        $releaseNotes = [
            [
                'category' => 'Fixed',
                'title' => 'User Management Quick Fixes',
                'description' => 'Resolved minor issues affecting user management functionalities.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Improved',
                'title' => 'Forgot Password Layout',
                'description' => 'Enhanced authentication user experience by adjusting the layout of the forgot password page.',
                'sort_order' => 2,
            ],
        ];

        foreach ($releaseNotes as $note) {
            $version->releaseNotes()->create($note);
        }
    }
}
