<?php

namespace App\Console\Commands\OfflineSearchFacility;

use App\Jobs\OfflineSearchFacility\ClientRecordJob;
use App\Models\OfflineSearchClientRecord;
use App\Traits\HasInfoLogChannel;
use App\Traits\HasNewgen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClientRecordCommand extends Command
{
    use HasInfoLogChannel, HasNewgen;

    protected $signature = 'osf:client-record {--fresh}';

    protected $description = 'Perform data dumping of Offline Search Client Records from both BFC and BMI SQL Servers to MySQL';

    public function handle()
    {
        $log = $this->getInfoLogChannel('offline_search_client_records');

        if ($this->option('fresh')) {
            OfflineSearchClientRecord::truncate();
            $log->info('🗑️ Table truncated successfully!');
        }

        $log->info('🚀 Offline Search Client Record Job started at ' . now()->toDateTimeString());

        try {
            $this->processSource('Newgen BMI', 'sqlsrv_bmilmsdb', $log);
            $this->processSource('Newgen BFC', 'sqlsrv_bfclmsdb', $log);
            $this->processSource('Finnone', 'oracle_lms', $log);

            $log->info('🎉 Finished processing both BFC and BMI sources');
        } catch (\Throwable $th) {
            $log->error('❌ Offline Search Client Record Job failed: ' . $th->getMessage());
            $log->error('Stack trace: ' . $th->getTraceAsString());
            $log->error('Message: ' . $th->getMessage());
            throw $th;
        }
    }

    protected function processSource(string $source, string $connection, $log)
    {
        $log->info("📊 Processing {$source} source...");

        $chunkSize = 10000;
        $chunkIndex = 0;
        $batch = [];

        $query = DB::connection($connection)
            ->table(DB::raw("({$this->getClientRecord($source)}) t"))
            ->orderBy('t.account_number')
            ->cursor();

        foreach ($query as $row) {
            $rowArray = (array) $row;
            $batch[] = $rowArray;

            if (count($batch) >= $chunkSize) {
                $chunkIndex++;
                ClientRecordJob::dispatch($batch, $source, $chunkIndex, 0)->onQueue('osf');

                $log->info("✅ Dispatched {$source} chunk {$chunkIndex} with " . count($batch) . ' records');
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $chunkIndex++;
            ClientRecordJob::dispatch($batch, $source, $chunkIndex, 0)->onQueue('osf');

            $log->info("✅ Dispatched final {$source} chunk {$chunkIndex} with " . count($batch) . ' records');
        }

        $log->info("🎉 Finished dispatching {$chunkIndex} chunks for {$source}");
    }
}
