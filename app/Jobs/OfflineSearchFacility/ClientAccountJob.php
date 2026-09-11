<?php

namespace App\Jobs\OfflineSearchFacility;

use App\Traits\HasInfoLogChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ClientAccountJob implements ShouldQueue
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
        $log = $this->getInfoLogChannel('offline_search_client_accounts');

        try {
            $log->info("▶️ Processing {$this->source} chunk {$this->chunkIndex} with " . count($this->items) . ' records');

            // Batch the WHERE IN queries to avoid MySQL packet size limits
            $accountNumbers = array_column($this->items, 'account_number');
            $clientIds = [];

            foreach (array_chunk($accountNumbers, 5000) as $chunk) {
                $results = DB::table('offline_search_client_records')
                    ->whereIn('account_number', $chunk)
                    ->pluck('id', 'account_number')
                    ->toArray();
                $clientIds = array_merge($clientIds, $results);
            }

            // Prepare data
            $preparedData = [];
            foreach ($this->items as $record) {
                if (isset($clientIds[$record['account_number']])) {
                    $record['client_id'] = $clientIds[$record['account_number']];
                    $preparedData[] = $record;
                }
            }

            if (empty($preparedData)) {
                $log->warning("⚠️ No matching client records for {$this->source} chunk {$this->chunkIndex}");
                return;
            }

            // Single transaction, no FK overhead
            DB::transaction(function () use ($preparedData) {
                foreach (array_chunk($preparedData, 1000) as $batch) {
                    DB::table('offline_search_client_accounts')
                        ->upsert($batch, ['client_id'], ['account_number', 'source']);
                }
            });

            $log->info("✅ Inserted {$this->source} chunk {$this->chunkIndex} with " . count($preparedData) . ' records');
        } catch (\Throwable $th) {
            $log->error("❌ Failed {$this->source} chunk {$this->chunkIndex}: " . $th->getMessage());
            throw $th;
        }
    }
}
