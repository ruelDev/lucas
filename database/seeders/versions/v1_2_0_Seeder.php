<?php

namespace Database\Seeders\versions;

use App\Models\AppVersion;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class v1_2_0_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $version = AppVersion::create([
            'version' => '1.2.0',
            'release_type' => 'MAJOR',
            'released_at' => Carbon::now(),
        ]);

        $releaseNotes = [
            [
                'category' => 'Refactored',
                'title' => 'Receipt Converter Frontend',
                'description' => 'Refactored the frontend structure of the Receipt Converter to improve maintainability and rendering efficiency.',
                'sort_order' => 1,
            ],
            [
                'category' => 'Improved',
                'title' => 'Backend Code Cleanup',
                'description' => 'Removed unnecessary backend code to reduce resource usage and improve system efficiency.',
                'sort_order' => 2,
            ],
            [
                'category' => 'Improved',
                'title' => 'Multiple Session Handling',
                'description' => 'Enhanced multiple session handling with caching mechanisms applied for improved performance and reliability.',
                'sort_order' => 3,
            ],
        ];

        foreach ($releaseNotes as $note) {
            $version->releaseNotes()->create($note);
        }
    }
}
