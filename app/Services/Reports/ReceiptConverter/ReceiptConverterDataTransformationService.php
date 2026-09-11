<?php

namespace App\Services\Reports\ReceiptConverter;

class ReceiptConverterDataTransformationService
{
    public function setReturnHeader($source)
    {
        if ($source === 'NEWGEN') {
            $returnHeader = [
                'LOAN_NO',
                'RECEIPT_MODE',
                'INSTRUMENT_NO',
                'BANK_ID',
                'BRANCH_ID',
                'BANK_ACCOUNT',
                'RECEIPT_DATE',
                'INSTRUMENT_DATE',
                'RECEIPT_AMOUNT',
                'TDS_AMOUNT',
                'RECEIPT_NO',
                'DEFAULT_BRANCH',
                'DEPOSIT_BANK',
                'DEPOSIT_BANK_BRANCH',
                'DEPOSIT_BANK_ACCOUNT',
                'MAKER_REMARKS',
                'MIS_NO',
                'Customer_Name',
                'RECEIVED_FROM',
                'Ack_receipt_no',
                'PDC_FLAG'
            ];
        } elseif ($source === 'FINNONE_QR') {
            $returnHeader = [
                'AGREEMENTNO',
                'PAYMENT_MODE',
                'RECEIPT_DATE',
                'RECEIPT_NUM',
                'RECEIPT_CHANNEL',
                'RECEIPT_AMT',
                'DEALING_BANKID',
            ];
        } elseif ($source === 'FINNONE_UA') {
            $returnHeader = [
                'AGREEMENTNO',
                'PAYMENT_MODE',
                'RECEIPT_DATE',
                'RECEIPT_NUM',
                'CHECK_NUMBER',
                'RECEIPT_CHANNEL',
                'RECEIPT_AMT',
                'DEALING_BANKID',
                'REMARKS',
                'MIS_ACCOUNT',
                'CUSTOMERNAME'
            ];
        }

        return $returnHeader;
    }

    public function assignDataToRow($data, $header)
    {
        $assoc = [];
        foreach ($header as $i => $key) {
            $assoc[$key] = isset($data[$i]) ? trim((string)$data[$i]) : null;
        }
        return $assoc;
    }

    public function createRowProcessor(array $header)
    {
        return [
            'header' => $header,
            'allRows' => [],
            'totalValid' => 0,
            'totalInvalid' => 0,
            'rowNumber' => 1,
            'source' => null,
            'returnHeader' => [],
        ];
    }

    public function updateProcessorValidityCounts(array &$processor, array $validated)
    {
        if ($validated['valid']) {
            $processor['totalValid']++;
        } else {
            $processor['totalInvalid']++;
        }
    }

    public function processRow(array &$processor, string $source, array $row, array $header, $dataValidationService, $file = null)
    {
        $assoc = $this->assignDataToRow($row, $header);
        
        $rowNumber = $processor['rowNumber'];

        $processor['returnHeader'] = $this->setReturnHeader($source);
        
        $validated = $dataValidationService->validateRow($assoc, $rowNumber, $source, $file);

        $processor['allRows'][] = $validated;
        $this->updateProcessorValidityCounts($processor, $validated);
        $processor['rowNumber']++;
    }

    public function getHeadersAndFieldMap($source, $category)
    {
        if ($source === 'NEWGEN') {
            $headers = [
                'LOAN_NO',
                'RECEIPT_MODE',
                'INSTRUMENT_NO',
                'BANK_ID',
                'BRANCH_ID',
                'BANK_ACCOUNT',
                'RECEIPT_DATE',
                'INSTRUMENT_DATE',
                'RECEIPT_AMOUNT',
                'TDS_AMOUNT',
                'RECEIPT_NO',
                'DEFAULT_BRANCH',
                'DEPOSIT_BANK',
                'DEPOSIT_BANK_BRANCH',
                'DEPOSIT_BANK_ACCOUNT',
                'MAKER_REMARKS',
                'MIS_NO',
                'Customer_Name',
                'RECEIVED_FROM',
                'Ack_receipt_no',
                'PDC_FLAG',
            ];

            $fieldMap = [
                'LOAN_NO' => 'LOAN_NO',
                'RECEIPT_MODE' => 'RECEIPT_MODE',
                'INSTRUMENT_NO' => 'INSTRUMENT_NO',
                'BANK_ID' => 'BANK_ID',
                'BRANCH_ID' => 'BRANCH_ID',
                'BANK_ACCOUNT' => 'BANK_ACCOUNT',
                'RECEIPT_DATE' => 'RECEIPT_DATE',
                'INSTRUMENT_DATE' => 'INSTRUMENT_DATE',
                'RECEIPT_AMOUNT' => 'RECEIPT_AMOUNT',
                'TDS_AMOUNT' => 'TDS_AMOUNT',
                'RECEIPT_NO' => 'RECEIPT_NO',
                'DEFAULT_BRANCH' => 'DEFAULT_BRANCH',
                'DEPOSIT_BANK' => 'DEPOSIT_BANK',
                'DEPOSIT_BANK_BRANCH' => 'DEPOSIT_BANK_BRANCH',
                'DEPOSIT_BANK_ACCOUNT' => 'DEPOSIT_BANK_ACCOUNT',
                'MAKER_REMARKS' => 'MAKER_REMARKS',
                'MIS_NO' => 'MIS_NO',
                'Customer_Name' => 'Customer_Name',
                'RECEIVED_FROM' => 'RECEIVED_FROM',
                'Ack_receipt_no' => 'Ack_receipt_no',
                'PDC_FLAG' => 'PDC_FLAG',
            ];
        } elseif ($source === 'FINNONE_QR') {
            $headers = [
                'AGREEMENTNO',
                'PAYMENT_MODE',
                'RECEIPT_DATE',
                'RECEIPT_NUM',
                'RECEIPT_CHANNEL',
                'RECEIPT_AMT',
                'DEALING_BANKID',
            ];

            $fieldMap = [
                'AGREEMENTNO' => 'AGREEMENTNO',
                'PAYMENT_MODE' => 'PAYMENT_MODE',
                'RECEIPT_DATE' => 'RECEIPT_DATE',
                'RECEIPT_NUM' => 'RECEIPT_NUM',
                'RECEIPT_CHANNEL' => 'RECEIPT_CHANNEL',
                'RECEIPT_AMT' => 'RECEIPT_AMT',
                'DEALING_BANKID' => 'DEALING_BANKID',
            ];
        } elseif ($source === 'FINNONE_UA') {
            $headers = [
                'AGREEMENTNO',
                'PAYMENT_MODE',
                'RECEIPT_DATE',
                'RECEIPT_NUM',
                'CHECK_NUMBER',
                'RECEIPT_CHANNEL',
                'RECEIPT_AMT',
                'DEALING_BANKID',
                'REMARKS',
                'MIS_ACCOUNT',
                'CUSTOMERNAME'
            ];

            $fieldMap = [
                'AGREEMENTNO' => 'AGREEMENTNO',
                'PAYMENT_MODE' => 'PAYMENT_MODE',
                'RECEIPT_DATE' => 'RECEIPT_DATE',
                'RECEIPT_NUM' => 'RECEIPT_NUM',
                'CHECK_NUMBER' => 'CHECK_NUMBER',
                'RECEIP_CHANNEL' => 'RECEIPT_CHANNEL',
                'RECEIPT_AMT' => 'RECEIPT_AMT',
                'DEALING_BANKID' => 'DEALING_BANKID',
                'REMARKS' => 'REMARKS',
                'MIS_ACCOUNT' => 'MIS_ACCOUNT',
                'CUSTOMERNAME' => 'CUSTOMERNAME'
            ];
        }

        if ($category === 'invalid') {
            $headers[] = 'REMARKS';
            $fieldMap['REMARKS'] = null;
        }

        return [$headers, $fieldMap];
    }
}
