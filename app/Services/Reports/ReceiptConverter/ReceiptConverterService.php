<?php

namespace App\Services\Reports\ReceiptConverter;

use Illuminate\Http\UploadedFile;

class ReceiptConverterService
{
    protected $fileReaderService;
    protected $dataValidationService;
    protected $dataTransformationService;
    protected $dataPersistenceService;
    protected $exportService;
    protected $processorService;

    public function __construct(
        ReceiptConverterFileReaderService $fileReaderService,
        ReceiptConverterDataValidationService $dataValidationService,
        ReceiptConverterDataTransformationService $dataTransformationService,
        ReceiptConverterDataPersistenceService $dataPersistenceService,
        ReceiptConverterExportService $exportService,
        ReceiptConverterProcessorService $processorService,
    )
    {
        $this->fileReaderService = $fileReaderService;
        $this->dataValidationService = $dataValidationService;
        $this->dataTransformationService = $dataTransformationService;
        $this->dataPersistenceService = $dataPersistenceService;
        $this->exportService = $exportService;
        $this->processorService = $processorService;
    }

    public function processFile(string $filePath, string $extension, UploadedFile $file)
    {
        return $this->processorService->parseAndValidateAll($filePath, $extension, $file);
    }

    public function exportToFile($collections, $source, $category, $originalFilename)
    {
        return $this->exportService->bulkConvert($collections, $source, $category, $originalFilename);
    }
}
