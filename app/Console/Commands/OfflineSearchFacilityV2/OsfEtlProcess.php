<?php

namespace App\Console\Commands\OfflineSearchFacilityV2;

use App\Jobs\OfflineSearchFacilityV2\ProcessCustomerDataJob;
use App\Jobs\OfflineSearchFacilityV2\ProcessLmsRecordDataJob;
use App\Jobs\OfflineSearchFacilityV2\ProcessLosRecordDataJob;
use App\Jobs\OfflineSearchFacilityV2\ProcessPaymentDataJob;
use App\Models\EtlProcessLog;
use App\Services\OfflineSearchFacility\OsfEtlProcessService;
use App\Traits\HasOsfApiRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OsfEtlProcess extends Command
{
    use HasOsfApiRequest;

    protected $osfEtlProcessService;

    protected $signature = 'osf:begin-etl-process';

    protected $description = 'Perform the whole orchestration of ETL Processes for the Offline Search Facility.';

    public function __construct(OsfEtlProcessService $osfEtlProcessService)
    {
        parent::__construct();
        $this->osfEtlProcessService = $osfEtlProcessService;
    }

    /**
     * MAIN ORCHESTRATOR OF THE COMMAND
     */
    public function handle()
    {
        Log::channel('offline_search_facility_orchestrator')
            ->info('ETL orchestration started');
            
        $this->processCustomerData();
        // $this->processLosRecordData();
        // $this->processLmsRecordData();
        // $this->processPaymentData();

        Log::channel('offline_search_facility_orchestrator')
            ->info('ETL orchestration completed dispatching');
    }

    /**
     * HANDLE TRACKING OF JOB PROCESSES
     */
    private function addEtlProcessLog($processName)
    {
        $etl = EtlProcessLog::firstOrCreate(
            [
                'process_name' => $processName
            ],
            [
                'last_successful_page'      => 0,
                'last_attempted_page'       => 0,
                'total_processed'           => 0,
                'last_run_status'           => 'idle',
            ]
        );

        if ($etl->last_run_status === 'failed') {
            $this->warn(
                "{$processName} last run failed on page " .
                "{$etl->last_attempted_page} . Resuming from that page."
            );

            return $etl->last_attempted_page;
        }

        return $etl->last_successful_page + 1;
    }

    /**
     * ACTIVATE THE CUSTOMER DATA ETL PROCESSES
     */
    private function processCustomerData()
    {
        $startingPage = $this->addEtlProcessLog('customer_data');

        ProcessCustomerDataJob::dispatch(
            $startingPage
        )
            ->onQueue('osf-customer');

        $this->info(
            "Customer ETL started from page {$startingPage}"
        );
    }

    /**
     * ACTIVATE THE LOS RECORDS DATA ETL PROCESSES
     */
    private function processLosRecordData()
    {
        $startingPage = $this->addEtlProcessLog('los_record_data');

        ProcessLosRecordDataJob::dispatch(
            $startingPage
        )
            ->onQueue('osf-los');

        $this->info(
            "LOS Record ETL started from page {$startingPage}"
        );
    }

    /**
     * ACTIVATE THE LMS RECORDS DATA ETL PROCESSES
     */
    private function processLmsRecordData()
    {
        $startingPage = $this->addEtlProcessLog('lms_record_data');

        ProcessLmsRecordDataJob::dispatch(
            $startingPage
        )
            ->onQueue('osf-lms');

        $this->info(
            "LMS Record ETL started from page {$startingPage}"
        );
    }

    /**
     * ACTIVATE THE PAYMENTS DATA ETL PROCESSES
     */
    private function processPaymentData()
    {
        $startingPage = $this->addEtlProcessLog('payment_data');

        ProcessPaymentDataJob::dispatch(
            $startingPage
        )
            ->onQueue('osf-payment');

        $this->info(
            "Payment Record ETL started from page {$startingPage}"
        );
    }
}
