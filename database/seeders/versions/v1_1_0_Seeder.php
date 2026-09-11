<?php

namespace Database\Seeders\versions;

use App\Models\AppVersion;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class v1_1_0_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $version = AppVersion::create([
            'version' => '1.1.0',
            'release_type' => 'MINOR',
            'released_at' => Carbon::now(),
        ]);

        $releaseNotes = [
            [
                'category' => 'Added',
                'title' => 'Application Version Display',
                'description' => 'Introduced feature to display the current application version and structured release notes within the system.',
                'sort_order' => 1,
            ],
        ];

        foreach ($releaseNotes as $note) {
            $version->releaseNotes()->create($note);
        }
    }
}
