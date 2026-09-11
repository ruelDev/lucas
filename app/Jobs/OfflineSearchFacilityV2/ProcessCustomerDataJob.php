<?php

namespace App\Jobs\OfflineSearchFacilityV2;

use App\Services\OfflineSearchFacility\OsfEtlProcessService;
use App\Traits\HasOsfApiRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCustomerDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasOsfApiRequest;

    public $timeout = 3600;

    public function __construct(
        public int $page
    ) {}

    public function handle(
        OsfEtlProcessService $service
    ): void {

        try {
            $service->markAttempted($this->page, 'customer_data');

            Log::channel('offline_search_facility_customer')
                ->info('Customer Job Started', [
                    'page' => $this->page,
                    'job_id' => optional($this->job)->getJobId(),
                ]);

            $response = $this->apiCustomerData($this->page);

            if ($response->status() === 429) {
                $retryAfter = $response->json('retry_after', 60);
                $delay = $retryAfter > 0 ? $retryAfter : 60;

                Log::channel('offline_search_facility_customer')
                    ->warning('Rate Limited, re-queuing page', [
                        'page'          => $this->page,
                        'retry_after'   => $delay
                    ]);

                ProcessCustomerDataJob::dispatch($this->page)
                    ->onQueue('osf-customer')
                    ->delay(now()->addSeconds($delay));

                return;
            }

            $response->throw();

            Log::channel('offline_search_facility_customer')
                ->info('Customer API Response Received', [
                    'page' => $this->page,
                    'status' => $response->status(),
                ]);

            $rows =  $response->json('data', []);

            if (empty($rows)) {
                Log::channel('offline_search_facility_customer')
                    ->warning(
                        "Page {$this->page} returned no data."
                    );
                
                $service->markCompleted('customer_data');

                return;
            }

            Log::channel('offline_search_facility_customer')
                ->info('Customer Upsert Starting', [
                    'page' => $this->page,
                    'records' => count($rows),
                ]);

            $start = microtime(true);
            $counts = $service->upsertCustomerData($rows);
            $duration = round((microtime(true) - $start) * 1000);

            Log::channel('offline_search_facility_customer')
                ->info('Customer Upsert Completed', [
                    'page'          => $this->page,
                    'fetched'       => count($rows),
                    'inserted'      => $counts['inserted'],
                    'updated'       => $counts['updated'],
                    'skipped'       => $counts['skipped'],
                    'duration_ms'   => $duration,
                ]);

            $service->updateProgress(
                $this->page,
                $counts['inserted'] + $counts['updated'],
                'customer_data'
            );

            $hasMore = $response->json(
                'meta.has_more',
                false
            );

            Log::channel('offline_search_facility_customer')
                ->info(
                    "Page {$this->page} processed. Records: " .
                        count($rows)
                );

            if ($hasMore) {
                ProcessCustomerDataJob::dispatch(
                    $this->page + 1
                )->onQueue('osf-customer');

                Log::channel('offline_search_facility_customer')
                    ->info(
                        "Queued next page: " .
                            ($this->page + 1)
                    );
            } else {
                $service->markCompleted('customer_data');
            }
        } catch (\Throwable $e) {
            $service->markFailed('customer_data');

            Log::channel('offline_search_facility_customer')
                ->error('Customer Job Failed', [
                    'page' => $this->page,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

            throw $e;
        }
    }
}
