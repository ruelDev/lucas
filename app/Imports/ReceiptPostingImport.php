<?php

namespace App\Imports;

use App\Models\ReceiptPostingDeposit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ReceiptPostingImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    SkipsEmptyRows,
    WithStartRow
{
    protected $validatedRows = [];
    protected $totalValid = 0;
    protected $totalInvalid = 0;
    protected $rowNumber = 0;

    // start reading from row 3 based on the xlsx template
    public function startRow(): int
    {
        return 2;
    }

    // process each chunk of rows
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rowNumber++;

            // convert row into array and normalize keys
            $rowArray = $row->toArray();
            $normalized = $this->normalizeRow($rowArray);
            // validate row
            $validated = $this->validateRow($normalized, $this->rowNumber);

            $this->validatedRows[] = $validated;

            if ($validated['valid']) {
                $this->totalValid++;
            } else {
                $this->totalInvalid++;
            }
        }
    }

    // normalize rows
    private function normalizeRow(array $row): array
    {
        return [
            'agreement_number' => $row['agreement_number'] ?? $row['AGREEMENT_NUMBER'] ?? null,
            'reference_number' => $row['reference_number'] ?? $row['REFERENCE_NUMBER'] ?? null,
            'mis_number' => $row['mis_number'] ?? $row['MIS_NUMBER'] ?? null,
            'customer_name' => $row['customer_name'] ?? $row['CUSTOMER_NAME'] ?? null,
            'aoc' => $row['aoc'] ?? $row['AOC'] ?? null,
            'ar_number' => $row['ar_number'] ?? $row['AR_NUMBER'] ?? null,
            'ar_amount' => $row['ar_amount'] ?? $row['AR_AMOUNT'] ?? null,
            'payment' => $row['payment'] ?? $row['PAYMENT'] ?? null,
            'payment_type' => $row['payment_type'] ?? $row['PAYMENT_TYPE'] ?? null,
            'npa_stage' => $row['npa_stage'] ?? $row['NPA_STAGE'] ?? null,
            'reason' => $row['reason'] ?? $row['REASON'] ?? null,
        ];
    }

    // validate per row
    private function validateRow(array $assoc, int $rowNumber): array
    {
        $validator = Validator::make($assoc, [
            'agreement_number' => ['required'],
            'reference_number' => ['required'],
            'mis_number' => ['required'],
            'customer_name' => ['required'],
            'aoc' => ['required'],
            'ar_number' => ['required'],
            'ar_amount' => ['required'],
            'payment' => ['required'],
            'payment_type' => ['required'],
            'npa_stage' => ['required'],
            'reason' => ['required']
        ]);

        $errors = $validator->errors()->all();

        return [
            'row' => $rowNumber,
            'AGREEMENT_NUMBER' => $assoc['agreement_number'],
            'REFERENCE_NUMBER' => $assoc['reference_number'],
            'MIS_NUMBER' => $assoc['mis_number'],
            'CUSTOMER_NAME' => $assoc['customer_name'],
            'AOC' => $assoc['aoc'],
            'AR_NUMBER' => $assoc['ar_number'],
            'AR_AMOUNT' => $assoc['ar_amount'],
            'PAYMENT' => $assoc['payment'],
            'PAYMENT_TYPE' => $assoc['payment_type'],
            'NPA_STAGE' => $assoc['npa_stage'],
            'REASON' => $assoc['reason'],
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    // process in chunks
    public function chunkSize(): int
    {
        return 1000;
    }

    // get all validated rows
    public function getValidatedRows(): array
    {
        return $this->validatedRows;
    }

    // get summary statistics
    public function getSummary(): array
    {
        return [
            'total' => $this->rowNumber,
            'valid' => $this->totalValid,
            'invalid' => $this->totalInvalid,
        ];
    }
}
