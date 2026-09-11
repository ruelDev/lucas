<?php

namespace App\Services\OfflineSearchFacility;

use Illuminate\Support\Facades\DB;

class OsfEtlProcessService
{
    public const CHUNK_SIZE_CUSTOMERS = 250;
    public const CHUNK_SIZE_LOS_RECORDS = 250;
    public const CHUNK_SIZE_LMS_RECORDS = 200;
    public const CHUNK_SIZE_PAYMENTS = 200;

    private function buildUpsertSql(
        string $table,
        array $columns,
        array $conflictKeys,
        array $updateColumns,
        int $rowCount
    ): string {
        $colList      = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $placeholder  = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $placeholders = implode(', ', array_fill(0, $rowCount, $placeholder));

        $updates = implode(', ', array_map(
            fn($c) => "`{$c}` = VALUES(`{$c}`)",
            $updateColumns
        ));

        return "INSERT INTO `{$table}` ({$colList}) VALUES {$placeholders}
            ON DUPLICATE KEY UPDATE {$updates}";
    }

    public function upsertCustomerData($rows)
    {
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $now = now();

        collect($rows)
            ->map(function ($row) use ($now) {

                $hash = hash('sha256', implode('|', [
                    $row['CUSTOMER_ID']         ?? '',
                    $row['FIRST_NAME']          ?? '',
                    $row['LAST_NAME']           ?? '',
                    $row['DATE_OF_BIRTH']       ?? '',
                ]));

                return [
                    'row_hash'    => $hash,
                    'customer_id' => $row['CUSTOMER_ID'],
                    'fname'       => $row['FIRST_NAME'],
                    'mname'       => $row['MIDDLE_NAME'],
                    'lname'       => $row['LAST_NAME'],
                    'dob'         => $row['DATE_OF_BIRTH'],
                    'address'     => $row['ADDRESS1'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            })
            ->chunk(self::CHUNK_SIZE_CUSTOMERS)
            ->each(function ($chunk) use (&$totalInserted, &$totalUpdated, &$totalSkipped) {
                DB::transaction(function () use ($chunk, &$totalInserted, &$totalUpdated, &$totalSkipped) {
                    $chunkSize = $chunk->count();
                    
                    $existingCount = DB::table('offline_search_facility_customers')
                        ->whereIn('row_hash', $chunk->pluck('row_hash')->toArray())
                        ->count();

                    $columns = [
                        'row_hash',
                        'customer_id',
                        'fname',
                        'mname',
                        'lname',
                        'dob',
                        'address',
                        
                    ];

                    $affected = DB::affectingStatement(
                        $this->buildUpsertSql(
                            'offline_search_facility_customers',
                            array_keys($chunk->first()),
                            ['row_hash'],
                            [
                                'fname',
                                'mname',
                                'lname',
                                'dob',
                                'address',
                                'updated_at'
                            ],
                            $chunkSize
                        ),
                        $chunk->flatMap(fn($row) => array_values($row))->toArray()
                    );

                    $inserted = $chunkSize - $existingCount;
                    $updated  = $affected - $inserted;
                    $skipped  = $chunkSize - $inserted - max(0, $updated);
                    $totalInserted += max(0, $inserted);
                    $totalUpdated  += max(0, $updated);
                    $totalSkipped  += max(0, $skipped);
                });
            });

        return [
            'inserted' => $totalInserted,
            'updated'  => $totalUpdated,
            'skipped'  => $totalSkipped,
        ];
    }

    public function upsertLosRecordData($rows)
    {
        collect($rows)
            ->map(function ($row) {

                $hash = hash('sha256', implode('|', [
                    $row['SOURCE']            ?? '',
                    $row['TID']               ?? '',
                    $row['STATUS']            ?? '',
                ]));

                return [
                    'row_hash'          => $hash,
                    'financing_bank'    => $row['SOURCE'],
                    'rlos_id'           => $row['TID'],
                    'status'            => $row['STATUS'],
                    'date_encoded'      => $row['DATE_ENCODED'],
                    'decision_date'     => $row['DATE_DECISION'],
                    'remarks'           => null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            })
            ->chunk(self::CHUNK_SIZE_LOS_RECORDS)
            ->each(function ($chunk) {
                DB::table('offline_search_facility_los_records')
                    ->upsert(
                        $chunk->toArray(),
                        ['row_hash'],
                        [
                            'financing_bank',
                            'status',
                            'date_encoded',
                            'decision_date',
                            'remarks',
                            'updated_at'
                        ]
                    );
            });
    }

    public function upsertLmsRecordData($rows)
    {
        collect($rows)
            ->map(function ($row) {

                $hash = hash('sha256', implode('|', [
                    $row['CUSTOMER_ID']             ?? '',
                    $row['LOAN_APPLICATION_ID']     ?? '',
                    $row['AGREEMENT_NO']            ?? '',
                    $row['ACCOUNT_NUMBER']          ?? '',
                    $row['AGREEMENT_ID']            ?? '',
                    $row['DATE_SOLD']               ?? '',
                    $row['REPAYMENT_STARTDATE']     ?? '',
                    $row['MATURITY_DATE']           ?? '',
                    $row['AMOUNT_FINANCE']          ?? '',
                    $row['TENURE']                  ?? '',
                    $row['EMI']                     ?? '',
                ]));

                return [
                    'row_hash'                  => $hash,
                    'customer_id'               => $row['CUSTOMER_ID'],
                    'loan_application_id'       => $row['LOAN_APPLICATION_ID'],
                    'agreement_no'              => $row['AGREEMENT_NO'],
                    'account_number'            => $row['ACCOUNT_NUMBER'],
                    'agreement_id'              => $row['AGREEMENT_ID'],
                    'date_sold'                 => $row['DATE_SOLD'],
                    'first_due_date'            => $row['REPAYMENT_STARTDATE'],
                    'maturity_date'             => $row['MATURITY_DATE'],
                    'loan_amount'               => $row['AMOUNT_FINANCE'],
                    'loan_term'                 => $row['TENURE'],
                    'emi'                       => $row['EMI'],
                    'loan_status'               => null,
                    'npa_stage'                 => $row['NPA_STAGED'],
                    'account_rating'            => null,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];
            })
            ->chunk(self::CHUNK_SIZE_LMS_RECORDS)
            ->each(function ($chunk) {
                DB::table('offline_search_facility_lms_records')
                    ->upsert(
                        $chunk->toArray(),
                        ['row_hash'],
                        [
                            'customer_id',
                            'loan_application_id',
                            'account_number',
                            'agreement_id',
                            'date_sold',
                            'first_due_date',
                            'maturity_date',
                            'loan_amount',
                            'loan_term',
                            'emi',
                            'loan_status',
                            'npa_stage',
                            'account_rating',
                            'updated_at'
                        ]
                    );
            });
    }

    public function upsertPaymentData($rows)
    {
        collect($rows)
            ->map(function ($row) {

                $hash = hash('sha256', implode('|', [
                    $row['LOAN_ID']        ?? '',
                    $row['RECEIPT_NO']     ?? '',
                    $row['PAYMENT_DATE']   ?? '',
                    $row['PAYMENT_AMOUNT'] ?? '',
                    $row['PAYMENT_TYPE']   ?? '',
                ]));

                return [
                    'row_hash'                  => $hash,
                    'loan_id'                   => $row['LOAN_ID'],
                    'payment_type'              => $row['PAYMENT_TYPE'],
                    'dealing_bank_id'           => $row['DEALING_BANK_ID'],
                    'payment_mode'              => $row['PAYMENT_MODE'],
                    'receipt_no'                => $row['RECEIPT_NO'],
                    'payment_amount'            => $row['PAYMENT_AMOUNT'],
                    'pdc_flag'                  => $row['PDC_FLAG'],
                    'status'                    => $row['STATUS'],
                    'payment_date'              => $row['PAYMENT_DATE'],
                    'deposit_date'              => $row['DEPOSIT_DATE'],
                    'bp_type'                   => $row['BP_TYPE'],
                    'bp_id'                     => $row['BP_ID'],
                    'remarks'                   => $row['REMARKS'],
                    'branch_id'                 => $row['BRANCH_ID'],
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];
            })
            ->chunk(self::CHUNK_SIZE_PAYMENTS)
            ->each(function ($chunk) {
                DB::table('offline_search_facility_payment_records')
                    ->upsert(
                        $chunk->toArray(),
                        ['row_hash'],
                        [
                            'loan_id',
                            'payment_type',
                            'dealing_bank_id',
                            'payment_mode',
                            'receipt_no',
                            'payment_amount',
                            'pdc_flag',
                            'status',
                            'payment_date',
                            'deposit_date',
                            'bp_type',
                            'bp_id',
                            'remarks',
                            'branch_id',
                            'updated_at',
                        ]
                    );
            });
    }

    public function updateProgress(
        int $page,
        int $processed,
        string $processName
    ): void {
        $pdo = DB::getPdo();

        $stmt = $pdo->prepare("
        INSERT INTO etl_process_logs
            (process_name, last_successful_page, last_attempted_page, total_processed, last_run_status, last_run_at, created_at, updated_at)
        VALUES
            (:process_name, :page, :page2, :processed, 'running', NOW(), NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            last_successful_page = VALUES(last_successful_page),
            last_attempted_page  = VALUES(last_attempted_page),
            last_run_status      = 'running',
            last_run_at          = NOW(),
            updated_at           = NOW(),
            total_processed      = total_processed + VALUES(total_processed)
    ");

        $stmt->execute([
            ':process_name' => $processName,
            ':page'         => $page,
            ':page2'        => $page,
            ':processed'    => (int) $processed,
        ]);
    }

    public function markAttempted(int $page, string $processName): void
    {
        $pdo = DB::getPdo();

        $stmt = $pdo->prepare("
        INSERT INTO etl_process_logs
            (process_name, last_attempted_page, last_successful_page, total_processed, last_run_status, last_run_at, created_at, updated_at)
        VALUES
            (:process_name, :page, 0, 0, 'running', NOW(), NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            last_attempted_page = VALUES(last_attempted_page),
            last_run_status     = 'running',
            last_run_at         = NOW(),
            updated_at          = NOW()
    ");

        $stmt->execute([
            ':process_name' => $processName,
            ':page'         => $page,
        ]);
    }

    public function markFailed(string $processName): void
    {
        DB::table('etl_process_logs')
            ->where('process_name', $processName)
            ->update([
                'last_run_status'       => 'failed',
                'updated_at'            => now(),
            ]);
    }

    public function markCompleted(string $processName): void
    {
        DB::table('etl_process_logs')
            ->where('process_name', $processName)
            ->update([
                'last_run_status'       => 'completed',
                'updated_at'            => now(),
            ]);
    }
}
