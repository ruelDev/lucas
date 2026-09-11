<?php

namespace App\Console\Commands\OfflineSearchFacility;

use App\Jobs\OfflineSearchFacility\LosRecordJob;
use App\Models\OfflineSearchLosRecord;
use App\Traits\HasInfoLogChannel;
use App\Traits\HasNewgen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LosRecordCommand extends Command
{
    use HasInfoLogChannel, HasNewgen;

    protected $signature = 'osf:los-record {--fresh}';

    protected $description = 'Perform data dumping of Offline Search LOS Records from both BFC and BMI SQL Servers to MySQL';

    public function handle()
    {
        $log = $this->getInfoLogChannel('offline_search_los_records');

        if ($this->option('fresh')) {
            OfflineSearchLosRecord::truncate();
            $log->info('🗑️ Table truncated successfully!');
        }

        $log->info('🚀 Offline Search LOS Record Job started at ' . now()->toDateTimeString());

        try {
            $this->processSource('Newgen BFC', 'sqlsrv_bfcnlsdb', $log);
            $this->processSource('Newgen BMI', 'sqlsrv_bminlsdb', $log);

            $log->info('🎉 Finished processing both BFC and BMI sources');
        } catch (\Throwable $th) {
            $log->error('❌ Offline Search Client Record Job failed: ' . $th->getMessage());
            $log->error('Stack trace: ' . $th->getTraceAsString());
            $log->error('Message: ' . $th->getMessage());
            throw $th;
        }
    }

    protected function processSource(string $financing, string $connection, $log)
    {
        $log->info("📊 Processing {$financing} financing...");

        $chunkSize = 10000;
        $chunkIndex = 0;
        $batch = [];

        $query = DB::connection($connection)
            ->table(DB::raw("({$this->getLosRecords()}) as t"))
            ->orderBy('t.rlos_id')
            ->cursor();

        foreach ($query as $row) {
            $rowArray = (array) $row;
            $rowArray['financing'] = $financing;
            $batch[] = $rowArray;

            if (count($batch) >= $chunkSize) {
                $chunkIndex++;
                LosRecordJob::dispatch($batch, $financing, $chunkIndex, 0)->onQueue('osf');

                $log->info("✅ Dispatched {$financing} chunk {$chunkIndex} with " . count($batch) . ' records');
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $chunkIndex++;
            LosRecordJob::dispatch($batch, $financing, $chunkIndex, 0)->onQueue('osf');

            $log->info("✅ Dispatched final {$financing} chunk {$chunkIndex} with " . count($batch) . ' records');
        }

        $log->info("🎉 Finished dispatching {$chunkIndex} chunks for {$financing}");
    }
}
