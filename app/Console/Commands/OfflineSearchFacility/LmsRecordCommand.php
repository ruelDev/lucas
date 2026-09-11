<?php

namespace App\Console\Commands\OfflineSearchFacility;

use App\Jobs\OfflineSearchFacility\LmsRecordJob;
use App\Models\OfflineSearchLmsRecord;
use App\Traits\HasInfoLogChannel;
use App\Traits\HasNewgen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LmsRecordCommand extends Command
{
    use HasInfoLogChannel, HasNewgen;

    protected $signature = 'osf:lms-record {--fresh}';

    protected $description = 'Perform data dumping of Offline Search LMS Records from both BFC and BMI SQL Servers to MySQL';

    public function handle()
    {
        $log = $this->getInfoLogChannel('offline_search_lms_records');

        if ($this->option('fresh')) {
            OfflineSearchLmsRecord::truncate();
            $log->info('🗑️ Table truncated successfully!');
        }

        $log->info('🚀 Offline Search LMS Record Job started at ' . now()->toDateTimeString());

        try {
            $this->processFinance('Newgen BFC', 'sqlsrv_bfclmsdb', $log);
            $this->processFinance('Newgen BMI', 'sqlsrv_bmilmsdb', $log);

            $log->info('🎉 Finished processing both BFC and BMI finances');
        } catch (\Throwable $th) {
            $log->error('❌ Offline Search LMS Record Job failed: ' . $th->getMessage());
            $log->error('Stack trace: ' . $th->getTraceAsString());
            $log->error('Message: ' . $th->getMessage());
            throw $th;
        }
    }

    protected function processFinance(string $financing, string $connection, $log)
    {
        $log->info("📊 Processing {$financing} financing...");

        $chunkSize = 10000;
        $chunkIndex = 0;
        $batch = [];

        $query = DB::connection($connection)
            ->table(DB::raw("({$this->getLmsRecords()}) as t"))
            ->orderBy('t.mis_no')
            ->cursor();

        foreach ($query as $row) {
            $rowArray = (array) $row;
            $rowArray['financing'] = $financing;
            $batch[] = $rowArray;

            if (count($batch) >= $chunkSize) {
                $chunkIndex++;
                LmsRecordJob::dispatch($batch, $financing, $chunkIndex, 0)->onQueue('osf');

                $log->info("✅ Dispatched {$financing} chunk {$chunkIndex} with " . count($batch) . ' records');
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $chunkIndex++;
            LmsRecordJob::dispatch($batch, $financing, $chunkIndex, 0)->onQueue('osf');

            $log->info("✅ Dispatched final {$financing} chunk {$chunkIndex} with " . count($batch) . ' records');
        }

        $log->info("🎉 Finished dispatching {$chunkIndex} chunks for {$financing}");
    }
}
