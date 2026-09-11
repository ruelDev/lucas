<?php

namespace Database\Seeders\versions;

use App\Models\AppVersion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class v1_0_1_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $version = AppVersion::firstOrCreate([
            'version' => '1.2.0',
            'release_type' => 'MAJOR',
            'released_at' => Carbon::now(),
        ]);

        $releaseNotes = [
            [
                'category' => 'Fixed Component',
                'title' => 'Receipt Converter',
                'description' => 'Restructured internal components to lighten the load of the front end for Receipt Converter.',
                'sort_order' => 1,
            ],
            [
                'category' => 'New Implementation',
                'title' => 'Redis for Session Caching',
                'description' => 'Migrated Sessiong handling from Database Session handling into Cache handling to reduce load time.',
                'sort_order' => 2,
            ],
            [
                'category' => 'Improvement',
                'title' => 'Multiple Session improved',
                'description' => 'Adapted Hybrid Multiple Session Checking via Database users active session version and Cache store for last user activity.',
                'sort_order' => 3,
            ],
            [
                'category' => 'Addition',
                'title' => 'New Permissions Added',
                'description' => 'New Permissions added for improved Role based access for Receipt converter.',
                'sort_order' => 4,
            ],
        ];

        foreach ($releaseNotes as $note) {
            $version->releaseNotes()->create($note);
        }
    }
}
