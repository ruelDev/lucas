<?php

namespace App\Services\Reports\ReceiptConverter;

use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class ReceiptConverterFileReaderService
{
    public function openCsvFile($filePath)
    {
        return fopen($filePath, 'r');
    }

    public function skipHeaderRows($handle, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            fgetcsv($handle);
        }
    }

    public function isEmptyRow(array $row)
    {
        return count(array_filter($row)) === 0;
    }

    public function normalizeEncoding(array $row)
    {
        return array_map(function ($value) {
            if ($value === null) {
                return null;
            }

            $encoding = mb_detect_encoding(
                $value,
                ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'],
                true
            );

            return ($encoding && $encoding !== 'UTF-8')
                ? mb_convert_encoding($value, 'UTF-8', $encoding)
                : $value;
        }, $row);
    }

    public function getRowDataFromCsv($filePath, $source, $header, $dataTransformationService, $dataValidationService, $file)
    {
        $handle = $this->openCsvFile($filePath);
        if (!$handle) {
            return $this->emptyResult();
        }

        try {
            $this->skipHeaderRows($handle, 3);

            $processor = $dataTransformationService->createRowProcessor($header);

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $row = $this->normalizeEncoding($row);

                $dataValidationService->scanLengthRawData($source, $row);

                $dataTransformationService->processRow($processor, $source, $row, $header, $dataValidationService, $file);

                if ($processor['rowNumber'] % 1000 === 0) {
                    gc_collect_cycles();
                }
            }
            return $processor;
        } catch (\Throwable $th) {
            throw new UnexpectedValueException('Kindly check the file before uploading again.');
        } finally {
            fclose($handle);
        }
    }

    public function emptyResult()
    {
        return [
            'allRows' => [],
            'totalValid' => 0,
            'totalInvalid' => 0,
            'source' => null,
            'returnHeader' => [],
            'rowNumber' => 0,
        ];
    }
}
