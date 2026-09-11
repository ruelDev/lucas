<?php

namespace App\Services\Reports\ReceiptConverter;

use App\Models\ReceiptConverterLog;

class ReceiptConverterDataPersistenceService
{
    public function logConversion($source, $originalFilename, $filename, $paddedSeries, $date, $category)
    {
        ReceiptConverterLog::insert([
            'source' => $source,
            'old_filename' => $originalFilename,
            'converted_filename' => $filename,
            'series' => $paddedSeries,
            'date' => $date,
            'remarks' => $category,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getLastUploadedLog($date)
    {
        return ReceiptConverterLog::where('date', $date)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getLastFinnoneValidUploadedLog($date)
    {
        return ReceiptConverterLog::where('date', $date)
            ->whereNot('source', 'NEWGEN')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
