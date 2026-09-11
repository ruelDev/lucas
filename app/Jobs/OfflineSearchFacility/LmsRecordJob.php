<?php

namespace App\Jobs\OfflineSearchFacility;

use App\Traits\HasInfoLogChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class LmsRecordJob implements ShouldQueue
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
        $log = $this->getInfoLogChannel('offline_search_lms_records');

        try {
            $log->info("▶️ Processing {$this->source} chunk {$this->chunkIndex} with " . count($this->items) . ' records');

            $batchSize = 1000;
            $total = count($this->items);
            $batches = array_chunk($this->items, $batchSize);

            $accountNumbers = array_column($this->items, 'mis_no');
            $clientIds = DB::table('offline_search_client_accounts')
                ->whereIn('account_number', $accountNumbers)
                ->where('source', 'Newgen')
                ->pluck('id', 'account_number')
                ->toArray();

            foreach ($batches as $i => $batchPayload) {

                DB::transaction(function () use ($batchPayload, $i, $total, $log, $clientIds) {
                    foreach ($batchPayload as &$record) {
                        $record['clientacct_id'] = $clientIds[$record['mis_no']];
                    }

                    $log->info($batchPayload);
                    DB::table('offline_search_lms_records')
                        ->upsert(
                            $batchPayload,
                            ['clientacct_id'],
                            ['mis_no', 'reference_no', 'agreement_id', 'agreement_no', 'date_sold', 'first_due_date', 'maturity_date', 'last_payment_date', 'loan_amount', 'loan_term', 'emi', 'loan_status', 'npa_stage', 'financing']
                        );
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
