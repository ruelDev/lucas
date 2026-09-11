<?php

namespace App\Services\Reports\ReceiptConverter;

use Illuminate\Http\UploadedFile;
use UnexpectedValueException;

class ReceiptConverterProcessorService
{
    protected $fileReaderService;
    protected $dataValidationService;
    protected $dataTransformationService;

    public function __construct(
        ReceiptConverterFileReaderService $fileReaderService,
        ReceiptConverterDataValidationService $dataValidationService,
        ReceiptConverterDataTransformationService $dataTransformationService,
    ) {
        $this->fileReaderService = $fileReaderService;
        $this->dataValidationService = $dataValidationService;
        $this->dataTransformationService = $dataTransformationService;
    }

    public function parseAndValidateAll(string $filePath, string $extension, UploadedFile $file)
    {
        if ($extension === 'csv') {
            return $this->parseCsvAll($filePath, $file);
        }
    }

    public function parseCsvAll(string $filePath, UploadedFile $file)
    {
        $originalFilename = $file->getClientOriginalName();

        if (strpos($originalFilename, 'NEWGEN') !== false) {
            $source = 'NEWGEN';
        } elseif (strpos($originalFilename, 'NEWGEN') !== true && strpos($originalFilename, 'QR') !== false) {
            $source = 'FINNONE_QR';
        } elseif (strpos($originalFilename, 'NEWGEN') !== true && strpos($originalFilename, 'UA') !== false) {
            $source = 'FINNONE_UA';
        } else {
            throw new UnexpectedValueException('Kindly check the file before uploading again.');
        }

        if ($source === 'FINNONE_UA') {

            $header = [
                'agreementno',
                'payment_mode',
                'receipt_date',
                'receipt_num',
                '',
                'receipt_amt',
                'receipt_details',
                'mis_no',
                'customer_name',
                'receipt_channel',
            ];
        } else {
            $header = [
                'agreementno',
                'payment_mode',
                'receipt_date',
                'receipt_num',
                '',
                'receipt_amt',
                'receipt_details',
                'receipt_channel',
            ];
        }

        $data = $this->fileReaderService->getRowDataFromCsv(
            $filePath,
            $source,
            $header,
            $this->dataTransformationService,
            $this->dataValidationService,
            $file
        );

        $totalRows = $data['rowNumber'] - 1;

        $data['allRows'] = $this->dataValidationService->checkForDuplicates($data['allRows'], $source);

        $data['totalValid'] = count(array_filter($data['allRows'], fn($row) => $row['valid'] === true));
        $data['totalInvalid'] = count(array_filter($data['allRows'], fn($row) => $row['valid'] === false));

        $summary = [
            'total' => $totalRows,
            'valid' => $data['totalValid'],
            'invalid' => $data['totalInvalid'],
        ];

        return [$data['allRows'], $summary, $source, $data['returnHeader']];
    }
}
