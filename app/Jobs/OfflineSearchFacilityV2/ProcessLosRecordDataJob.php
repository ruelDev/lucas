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

class ProcessLosRecordDataJob implements ShouldQueue
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
            $service->markAttempted($this->page, 'los_record_data');

            Log::channel('offline_search_facility_los_records')
                ->info('LOS Records Job Started', [
                    'page' => $this->page,
                    'job_id' => optional($this->job)->getJobId(),
                ]);

            $response = $this->apiLosRecordData($this->page);

            if ($response->status() === 429) {
                $retryAfter = $response->json('retry_after', 60);
                $delay = $retryAfter > 0 ? $retryAfter : 60;

                Log::channel('offline_search_facility_los_records')
                    ->warning('Rate Limited, re-queuing page', [
                        'page'          => $this->page,
                        'retry_after'   => $delay
                    ]);

                ProcessLosRecordDataJob::dispatch($this->page)
                    ->onQueue('osf-los')
                    ->delay(now()->addSeconds($delay));

                return;
            }

            $response->throw();

            Log::channel('offline_search_facility_los_records')
                ->info('LOS Records API Response Received', [
                    'page' => $this->page,
                    'status' => $response->status(),
                ]);

            $rows = $response->json('data', []);

            if (empty($rows)) {
                Log::channel('offline_search_facility_los_records')
                    ->warning(
                        "Page {$this->page} returned no data."
                    );

                $service->markCompleted('los_record_data');

                return;
            }

            Log::channel('offline_search_facility_los_records')
                ->info('LOS Records Upsert Starting', [
                    'page' => $this->page,
                    'records' => count($rows),
                ]);

            $start = microtime(true);
            $service->upsertLosRecordData($rows);
            $duration = round((microtime(true) - $start) * 1000);

            Log::channel('offline_search_facility_los_records')
                ->info('LOS Records Upsert Completed', [
                    'page' => $this->page,
                    'records' => count($rows),
                    'duration_ms' => $duration,
                ]);

            $service->updateProgress(
                $this->page,
                count($rows),
                'los_record_data'
            );

            $hasMore = $response->json(
                'meta.has_more',
                false
            );

            Log::channel('offline_search_facility_los_records')
                ->info(
                    "Page {$this->page} processed. Records: " .
                        count($rows)
                );

            if ($hasMore) {
                ProcessLosRecordDataJob::dispatch(
                    $this->page + 1
                )->onQueue('osf-los');

                Log::channel('offline_search_facility_los_records')
                    ->info(
                        "Queued next page: " .
                            ($this->page + 1)
                    );
            } else {
                $service->markCompleted('los_record_data');
            }
        } catch (\Throwable $e) {
            $service->markFailed('los_record_data');

            Log::channel('offline_search_facility_los_records')
                ->error('LOS Records Job Failed', [
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
