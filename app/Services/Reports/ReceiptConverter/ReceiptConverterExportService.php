<?php

namespace App\Services\Reports\ReceiptConverter;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ReceiptConverterExportService
{
    protected $dataTransformationService;
    protected $dataPersistenceService;

    public function __construct(
        ReceiptConverterDataTransformationService $dataTransformationService,
        ReceiptConverterDataPersistenceService $dataPersistenceService,
    ) {
        $this->dataTransformationService = $dataTransformationService;
        $this->dataPersistenceService = $dataPersistenceService;
    }

    public function bulkConvert(array $collections, string $source, string $category, string $originalFilename)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'receipts_') . '.xlsx';

        $writer = new Writer();
        $writer->openToFile($tempFile);

        [$headers, $fieldMap] = $this->dataTransformationService->getHeadersAndFieldMap($source, $category);

        $this->writeHeaderRow($writer, $headers);
        $this->writeDataRows($writer, $collections, $headers, $fieldMap);

        $writer->close();

        $filename = $this->setExportFilename($source, $category, $originalFilename);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'GENERATED CONVERTED ' . $source . ' FILE',
            'model' => null,
            'module' => 'Receipt Converter',
            'old_data' => null,
            'new_data' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    private function writeHeaderRow($writer, array $headers)
    {
        $headerCells = array_map(fn($h) => Cell::fromValue($h), $headers);
        $writer->addRow(new Row($headerCells));
    }

    private function setValuePerHeader(array $headers, array $row, array $fieldMap): array
    {
        $cells = [];

        foreach ($headers as $header) {
            if ($header === 'REMARKS') {
                $value = isset($row['errors']) && is_array($row['errors'])
                    ? implode(';', $row['errors'])
                    : '';
            } else {
                $field = $fieldMap[$header] ?? $header;
                $value = $row[$field] ?? '';
            }

            if (is_array($value)) {
                $value = implode('; ', $value);
            }

            $cells[] = Cell::fromValue($value);
        }

        return $cells;
    }

    private function writeDataRows($writer, array $collections, array $headers, array $fieldMap)
    {
        $bodyRowStyle = (new Style())
            ->setShouldWrapText(false);

        foreach ($collections as $row) {

            $cells = $this->setValuePerHeader($headers, $row, $fieldMap);

            $writer->addRow(new Row($cells, $bodyRowStyle));

            if (count($collections) > 5000) {
                gc_collect_cycles();
            }
        }
    }

    private function prepareFileNamePadding($source, $category, $lastValidFinnone)
    {
        if ($source !== 'NEWGEN') {
            if ($category === 'valid') {
                $nextSeries = $lastValidFinnone
                    ? $lastValidFinnone->series + 1
                    : 1;

                $paddedSeries = str_pad($nextSeries, 5, '0', STR_PAD_LEFT);
            } else {
                $paddedSeries = str_pad($lastValidFinnone->series, 5, '0', STR_PAD_LEFT);
            }
        } else {
            $paddedSeries = '00000';
        }

        return $paddedSeries;
    }

    private function finalizeFileName($source, $originalFilename, $fileNameDate, $paddedSeries, $category)
    {
        if ($source === 'FINNONE_QR' || $source === 'FINNONE_UA') {
            if (stripos($originalFilename, 'QR') !== false) {
                $filename = "QR_LTAD_{$fileNameDate}_{$paddedSeries}_{$category}.csv";
            } elseif (stripos($originalFilename, 'UA') !== false) {
                $filename = "UA_CMC_{$fileNameDate}_{$paddedSeries}_{$category}.csv";
            } else {
                Log::channel('receipt_converter')->error('Invalid File Source');
            }
        } elseif ($source === 'NEWGEN') {
            $pathInfo = pathinfo($originalFilename);
            $baseName = $pathInfo['filename'];

            $filename = "{$baseName}_converted_{$category}.xlsx";
        }

        return $filename;
    }

    private function setExportFilename($source, $category, $originalFilename)
    {
        $fileNameDate = now()->format('mdy');
        $logDate = now()->format('Y-m-d');

        try {
            $lastValidFinnone = $this->dataPersistenceService
                ->getLastFinnoneValidUploadedLog($logDate);

            $paddedSeries = $this->prepareFileNamePadding($source, $category, $lastValidFinnone);

            $filename = $this->finalizeFileName($source, $originalFilename, $fileNameDate, $paddedSeries, $category);

            $this->dataPersistenceService->logConversion($source, $originalFilename, $filename, $paddedSeries, $logDate, $category);

            return $filename;
        } catch (\Throwable $th) {
            Log::channel('receipt_converter')->error('Cannot set Export File name: ' . $th->getMessage());
        }
    }
}
