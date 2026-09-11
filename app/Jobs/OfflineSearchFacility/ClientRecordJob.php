<?php

namespace App\Jobs\OfflineSearchFacility;

use App\Traits\HasInfoLogChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ClientRecordJob implements ShouldQueue
{
    use Dispatchable, HasInfoLogChannel, InteractsWithQueue, Queueable, SerializesModels;

    protected array $items;
    protected string $source;
    protected int $chunkIndex;
    protected int $retryCount;

    public function __construct(array $items, string $source, int $chunkIndex, int $retryCount)
    {
        $this->items = $items;
        $this->source = $source;
        $this->chunkIndex = $chunkIndex;
        $this->retryCount = $retryCount;
    }

    public function handle(): void
    {
        $log = $this->getInfoLogChannel('offline_search_client_records');

        try {
            $log->info("▶️ Processing {$this->source} chunk {$this->chunkIndex} with " . count($this->items) . ' records');

            $batchSize = 2000;
            $total = count($this->items);
            $batches = array_chunk($this->items, $batchSize);

            foreach ($batches as $i => $batchPayload) {
                DB::transaction(function () use ($batchPayload, $i, $total, $log) {
                    DB::table('offline_search_client_records')->insert($batchPayload);

                    $log->info("✅ Inserted {$this->source} sub-batch " . ($i + 1) . ' of ' . ceil($total / 1000) . " in chunk {$this->chunkIndex} with " . count($batchPayload) . ' records');
                });
            }
        } catch (\Throwable $th) {
            $log->error("❌ Failed to process {$this->source} chunk {$this->chunkIndex}");
            $log->error($th->getMessage());
            throw $th;
        }
    }
}
