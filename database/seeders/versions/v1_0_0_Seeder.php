<?php

namespace Database\Seeders\versions;

use App\Models\AppVersion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class v1_0_0_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $version = AppVersion::create([
            'version' => '1.0.0',
            'release_type' => 'MAJOR',
            'released_at' => Carbon::now(),
        ]);

        $releaseNotes = [
            [
                'category' => 'Added',
                'title' => 'Initial Release',
                'description' => 'First Build release of Loans Unified Core Auxiliary System.',
                'sort_order' => 1,
            ],
        ];

        foreach ($releaseNotes as $note) {
            $version->releaseNotes()->create($note);
        }
    }
}
