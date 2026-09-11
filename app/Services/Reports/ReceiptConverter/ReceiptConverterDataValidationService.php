<?php

namespace App\Services\Reports\ReceiptConverter;

use App\Models\BankAccount;
use App\Models\Dealer;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use UnexpectedValueException;

class ReceiptConverterDataValidationService
{
    protected $dataTransformationService;
    protected $defaultRegexFormat = 'regex:/^\d+$/';

    public function __construct(
        ReceiptConverterDataTransformationService $dataTransformationService
    ) {
        $this->dataTransformationService = $dataTransformationService;
    }

    public function getNewgenBankAccountDetails($dealer)
    {
        if ($dealer) {
            $bankAccount = str_replace(" ", '', $dealer->bank_account . 'CASH');
            $bank = BankAccount::where('bank_account', $bankAccount)->first();

            $deposit_bank = $bank->bank_id;
            $deposit_bank_branch = $bank->bank_branch_id;
            $deposit_bank_account = $bank->bank_account;
        } else {
            $deposit_bank = 22;
            $deposit_bank_branch = 24;
            $deposit_bank_account = 'UNKNOWNCASH';
        }

        return [
            $deposit_bank,
            $deposit_bank_branch,
            $deposit_bank_account
        ];
    }

    public function setFinnoneBankId($originalFilename, $rawData)
    {
        if (strpos($originalFilename, 'QR') !== false) {
            if (substr($rawData['agreementno'], 0, 3) === 'MBF') {
                $bank_id = 471;
            } else {
                $bank_id = 469;
            }
        } elseif (strpos($originalFilename, 'UA') !== false) {
            $bank_id = 470;
        }

        return $bank_id;
    }

    public function prepareValidateRow($source, $rawData, $rowNumber, $originalFilename)
    {
        if ($source === 'NEWGEN') {

            $dealer = Dealer::where('dealer_code', $rawData['receipt_channel'])->first();

            [$deposit_bank, $deposit_bank_branch, $deposit_bank_account] = $this->getNewgenBankAccountDetails($dealer);

            $ack_receipt_no = str_replace([' ', 'AR-', 'AR'], '', $rawData['receipt_num']);

            $rowData = [
                'row' => $rowNumber,
                'LOAN_NO' => $rawData['agreementno'],
                'RECEIPT_MODE' => $rawData['payment_mode'],
                'INSTRUMENT_NO' => '',
                'BANK_ID' => '',
                'BANK_ACCOUNT' => '',
                'RECEIPT_DATE' => (new DateTime($rawData['receipt_date']))->format('n-j-Y'),
                'INSTRUMENT_DATE' => '',
                'RECEIPT_AMOUNT' => $rawData['receipt_amt'],
                'TDS_AMOUNT' => 0,
                'RECEIPT_NO' => "AUTO",
                'DEFAULT_BRANCH' => 'HEAD OFFICE',
                'DEPOSIT_BANK' => $deposit_bank,
                'DEPOSIT_BANK_BRANCH' => $deposit_bank_branch,
                'DEPOSIT_BANK_ACCOUNT' => $deposit_bank_account,
                'MAKER_REMARKS' => $rawData['receipt_details'],
                'MIS_NO' => '',
                'Customer_Name' => '',
                'RECEIVED_FROM' => $rawData['receipt_channel'],
                'Ack_receipt_no' => $ack_receipt_no,
                'PDC_FLAG' => 'N',
            ];
        } elseif ($source === 'FINNONE_QR') {
            $trimmed_raw_receipt_amt = str_replace(' ', '', $rawData['receipt_num']);
            $hasArPrefix = preg_match('/AR-?\d+/i', $trimmed_raw_receipt_amt);

            if ($hasArPrefix) {
                $cleaned_raw_receipt_amt = "'" . preg_replace('/.*AR-?/i', '', $trimmed_raw_receipt_amt);
            } else {
                $cleaned_raw_receipt_amt = null;
            }

            $bank_id = $this->setFinnoneBankId($originalFilename, $rawData);

            $rowData = [
                'row' => $rowNumber,
                'AGREEMENTNO' => $rawData['agreementno'],
                'PAYMENT_MODE' => $rawData['payment_mode'],
                'RECEIPT_DATE' => $rawData['receipt_date'],
                'RECEIPT_NUM' => $cleaned_raw_receipt_amt,
                'RECEIPT_CHANNEL' => $rawData['receipt_channel'],
                'RECEIPT_AMT' => $rawData['receipt_amt'],
                'DEALING_BANKID' => $bank_id,
            ];
        } elseif ($source === 'FINNONE_UA') {
            $trimmed_raw_receipt_amt = str_replace(' ', '', $rawData['receipt_num']);
            $hasArPrefix = preg_match('/AR-?\d+/i', $trimmed_raw_receipt_amt);

            if ($hasArPrefix) {
                $cleaned_raw_receipt_amt = "'" . preg_replace('/.*AR-?/i', '', $trimmed_raw_receipt_amt);
            } else {
                $cleaned_raw_receipt_amt = null;
            }

            $bank_id = $this->setFinnoneBankId($source, $rawData);

            $defaultAgreementNo = '0';

            $rowData = [
                'row' => $rowNumber,
                'AGREEMENTNO' => $rawData['agreementno'] ?: $defaultAgreementNo,
                'PAYMENT_MODE' => $rawData['payment_mode'],
                'RECEIPT_DATE' => $rawData['receipt_date'],
                'RECEIPT_NUM' => $cleaned_raw_receipt_amt,
                'CHECK_NUMBER' => 'N',
                'RECEIPT_CHANNEL' => $rawData['receipt_channel'],
                'RECEIPT_AMT' => $rawData['receipt_amt'],
                'DEALING_BANKID' => $bank_id,
                'REMARKS' => $rawData['receipt_details'],
                'MIS_ACCOUNT' => $rawData['mis_no'],
                'CUSTOMERNAME' => $rawData['customer_name'],
            ];
        }

        return $rowData;
    }

    private $receiptDate = 'RECEIPT DATE';

    private function validateNewgenRow(array $rowData): array
    {
        $validator = Validator::make($rowData, [
            'LOAN_NO'        => ['required', 'size:20'],
            'RECEIPT_MODE'   => ['required', 'in:C'],
            'RECEIPT_DATE'   => ['required', 'date'],
            'RECEIPT_AMOUNT' => ['required'],
            'RECEIVED_FROM'  => ['required', $this->defaultRegexFormat],
            'Ack_receipt_no' => ['required'],
            'MAKER_REMARKS'  => ['required'],
        ], [], [
            'LOAN_NO' => 'LOAN NO',
            'RECEIPT_MODE' => 'RECEIPT MODE',
            'RECEIPT_DATE' => $this->receiptDate,
            'RECEIPT_AMOUNT' => 'RECEIPT AMOUNT',
            'RECEIVED_FROM' => 'RECEIVED FROM',
            'Ack_receipt_no' => 'Ack Receipt No',
            'MAKER_REMARKS' => 'MAKER REMARKS'
        ]);

        return $validator->errors()->all();
    }

    private function validateFinnoneQrRow(array $rowData): array
    {
        $validator = Validator::make($rowData, [
            'AGREEMENTNO'  => ['required', 'size:18'],
            'PAYMENT_MODE' => ['required', 'in:C'],
            'RECEIPT_DATE' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        $date = Carbon::parse($value);
                        $now  = Carbon::now();
                        if ($date->month !== $now->month || $date->year !== $now->year) {
                            $fail("The $attribute must be within the current month and year.");
                        }
                    } catch (\Exception $e) {
                        $fail("The $attribute is not a valid date.");
                    }
                }
            ],
            'RECEIPT_NUM'     => ['required', 'regex:/^\'\d+$/'],
            'RECEIPT_CHANNEL' => ['required', $this->defaultRegexFormat, 'size:4'],
            'RECEIPT_AMT'     => ['required'],
        ], [], [
            'AGREEMENTNO' => 'AGREEMENTNO',
            'PAYMENT_MODE' => 'PAYMENT MODE',
            'RECEIPT_DATE' => $this->receiptDate,
            'RECEIPT_NUM' => 'RECEIPT NUM',
            'RECEIPT_CHANNEL' => 'RECEIPT CHANNEL',
            'RECEIPT_AMT' => 'RECEIPT AMT'
        ]);

        return $validator->errors()->all();
    }

    private function validateFinnoneUaRow(array $rowData): array
    {
        $validator = Validator::make($rowData, [
            'AGREEMENTNO'   => ['required'],
            'PAYMENT_MODE'  => ['required', 'in:C'],
            'RECEIPT_DATE'  => ['required', 'date'],
            'RECEIPT_NUM'   => ['required', 'regex:/^\'\d+$/'],
            'RECEIPT_CHANNEL' => ['required', $this->defaultRegexFormat],
            'RECEIPT_AMT'   => ['required'],
            'REMARKS'       => ['required'],
            'MIS_ACCOUNT'   => ['required'],
            'CUSTOMERNAME'  => ['required'],
        ], [], [
            'AGREEMENTNO' => 'AGREEMENTNO',
            'PAYMENT_MODE' => 'PAYMENT MODE',
            'RECEIPT_DATE' => $this->receiptDate,
            'RECEIPT_NUM' => 'RECEIPT NUM',
            'RECEIPT_CHANNEL' => 'RECEIPT CHANNEL',
            'RECEIPT_AMT' => 'RECEIPT AMT',
            'REMARKS' => 'REMARKS',
            'MIS_ACCOUNT' => 'MIS ACCOUNT',
            'CUSTOMERNAME' => 'CUSTOMERNAME'
        ]);

        return $validator->errors()->all();
    }

    public function validateRow($assoc, $rowNumber, $source, UploadedFile $file)
    {
        $originalFilename = $file->getClientOriginalName();

        $agreementNo = $assoc['agreementno'] ?? null;

        if ($source === 'FINNONE_UA' && blank($agreementNo)) {
            $agreementNo = '0';
        }

        $rawData = [
            'agreementno' => $agreementNo,
            'payment_mode' => $assoc['payment_mode'] ?? null,
            'receipt_date' => $assoc['receipt_date'] ?? null,
            'receipt_num' => $assoc['receipt_num'] ?? null,
            'null' => $assoc[''] ?? null,
            'receipt_amt' => $assoc['receipt_amt'] ?? null,
            'receipt_details' => $assoc['receipt_details'] ?? null,
            'mis_no' => $assoc['mis_no'] ?? null,
            'customer_name' => $assoc['customer_name'] ?? null,
            'receipt_channel' => $assoc['receipt_channel'] ?? null,
        ];
        $rowData = $this->prepareValidateRow($source, $rawData, $rowNumber, $originalFilename);

        $errors = match ($source) {
            'NEWGEN'      => $this->validateNewgenRow($rowData),
            'FINNONE_QR'  => $this->validateFinnoneQrRow($rowData),
            'FINNONE_UA'  => $this->validateFinnoneUaRow($rowData),
            default       => ["Unknown source: $source"],
        };

        $rowData['valid'] = count($errors) === 0;
        $rowData['errors'] = $errors;

        return $rowData;
    }

    private function duplicateKeyColumns(string $source): array
    {
        return match ($source) {
            'NEWGEN' => [
                'LOAN_NO',
                'RECEIPT_DATE',
                'RECEIPT_NO',
                'RECEIPT_AMOUNT',
                'RECEIVED_FROM',
                'Ack_receipt_no',
            ],

            'FINNONE_QR' => [
                'AGREEMENTNO',
                'RECEIPT_DATE',
                'RECEIPT_NUM',
                'RECEIPT_CHANNEL',
                'RECEIPT_AMT',
            ],

            'FINNONE_UA' => [
                'AGREEMENTNO',
                'RECEIPT_DATE',
                'RECEIPT_NUM',
                'CHECK_NUMBER',
                'RECEIPT_CHANNEL',
                'RECEIPT_AMT',
            ],

            default => [],
        };
    }


    private function buildPartialRowKey(array $row, array $columns): string
    {
        $keyData = [];

        foreach ($columns as $column) {
            $value = $row[$column] ?? null;

            $keyData[$column] = is_string($value)
                ? trim(mb_strtolower($value))
                : $value;
        }

        ksort($keyData);

        return hash('sha256', json_encode($keyData));
    }

    private function buildReceiptKey(array $receiptData): string
    {
        return implode('|', [
            $receiptData['receiptNum'],
            $receiptData['receiptDate'],
            $receiptData['receiptChannel']
        ]);
    }

    private function extractReceiptData(array $row): array
    {
        // Handle different column naming conventions
        $isLoanNoFormat = isset($row['LOAN_NO']);

        return [
            'receiptNum' => $isLoanNoFormat
                ? ($row['Ack_receipt_no'] ?? '')
                : ($row['RECEIPT_NUM'] ?? ''),
            'receiptDate' => $isLoanNoFormat
                ? ($row['receipt_date'] ?? '')
                : ($row['RECEIPT_DATE'] ?? ''),
            'receiptChannel' => $isLoanNoFormat
                ? ($row['RECEIVED_FROM'] ?? '')
                : ($row['RECEIPT_CHANNEL'] ?? ''),
            'agreementNo' => $isLoanNoFormat
                ? ($row['LOAN_NO'] ?? '')
                : ($row['AGREEMENTNO'] ?? '')
        ];
    }

    public function checkForDuplicates(array $rows, string $source): array
    {
        $fullRowGroups = [];
        $duplicateColumns = $this->duplicateKeyColumns($source);

        foreach ($rows as $index => $row) {
            if (!empty($duplicateColumns)) {
                $partialKey = $this->buildPartialRowKey($row, $duplicateColumns);
                $fullRowGroups[$partialKey][] = $index;
            }
        }

        $rows = $this->markExactDuplicates($rows, $fullRowGroups);

        $rows = $this->markReceiptDuplicates($rows, $this->rebuildReceiptGroups($rows));

        return $rows;
    }

    private function rebuildReceiptGroups(array $rows): array
    {
        $receiptGroups = [];
        $seenExactKeys = [];

        foreach ($rows as $index => $row) {
            $receiptData = $this->extractReceiptData($row);
            $receiptKey = $this->buildReceiptKey($receiptData);

            // For exact duplicates, only let the first occurrence participate
            $isExactDuplicate = in_array(
                'Duplicate Record: The entire row is an exact duplicate of another record.',
                $row['errors'] ?? []
            );

            if ($isExactDuplicate) {
                if (isset($seenExactKeys[$receiptKey])) {
                    continue; // skip subsequent duplicates, first one already added
                }
                $seenExactKeys[$receiptKey] = true;
            }

            $receiptGroups[$receiptKey][] = [
                'index' => $index,
                'agreementNo' => $receiptData['agreementNo'],
                'receiptData' => $receiptData,
            ];
        }

        return $receiptGroups;
    }

    private function markReceiptDuplicates(array $rows, array $receiptGroups): array
    {
        foreach ($receiptGroups as $group) {
            if (count($group) <= 1) {
                continue;
            }

            $agreementNumbers = array_unique(array_column($group, 'agreementNo'));

            if (count($agreementNumbers) <= 1) {
                continue;
            }

            $receiptData = $group[0]['receiptData'];
            $errorMessage = sprintf(
                "Multiple Records: Same Receipt No. (%s), Receipt Date (%s), Receipt Channel (%s) " .
                    "but different Agreement Numbers (%s)",
                $receiptData['receiptNum'],
                $receiptData['receiptDate'],
                $receiptData['receiptChannel'],
                implode(', ', $agreementNumbers)
            );

            foreach ($group as $item) {
                $rows[$item['index']]['errors'][] = $errorMessage;
                $rows[$item['index']]['valid'] = false;
            }
        }

        return $rows;
    }

    private function markExactDuplicates(array $rows, array $fullRowGroups): array
    {
        foreach ($fullRowGroups as $indices) {
            if (count($indices) <= 1) {
                continue;
            }

            $errorMessage = "Duplicate Record: The entire row is an exact duplicate of another record.";

            foreach ($indices as $index) {
                $rows[$index]['errors'][] = $errorMessage;
                $rows[$index]['valid'] = false;
            }
        }

        return $rows;
    }

    public function scanLengthRawData($source, $row)
    {
        $length = [
            'FINNONE_UA' => [10],
            'FINNONE_QR' => [8],
            'NEWGEN' => [8, 9]
        ];

        if (!isset($length[$source])) {
            throw new UnexpectedValueException("Invalid Source");
        }

        if (!in_array(count($row), $length[$source])) {
            throw new UnexpectedValueException('Length is not the same');
        }
    }
}
