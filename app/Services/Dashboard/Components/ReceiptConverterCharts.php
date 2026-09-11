<?php

namespace App\Services\Dashboard\Components;

use App\Models\AuditLog;
use App\Models\ReceiptConverterLog;
use Illuminate\Support\Facades\DB;

class ReceiptConverterCharts
{
    public function prepareReceiptConverterChartsData()
    {
        $query = ReceiptConverterLog::query();
        $logQuery = AuditLog::query();

        [
            $totalConvertedCount,
            $totalNewgenQrUaCount,
            $totalFinnoneQrCount,
            $totalFinnoneUaCount
        ] = $this->getTotalConvertedGenerations($query);

        $recentlyGeneratedFiles = $this->getRecentGenerations($query);

        $authoredUserGenerations = $this->getAuthorsPerGeneration($logQuery);

        $getGenerationLogs = $this->getGenerationLogs($query);

        return [
            'totalConvertedCount' => $totalConvertedCount,
            'totalNewgenQrUaCount' => $totalNewgenQrUaCount,
            'totalFinnoneQrCount' => $totalFinnoneQrCount,
            'totalFinnoneUaCount' => $totalFinnoneUaCount,
            'recentlyGeneratedFiles' => $recentlyGeneratedFiles,
            'authoredUserGenerations' => $authoredUserGenerations,
            'converterGenerationLogs' => $getGenerationLogs
        ];
    }

    private function getTotalConvertedGenerations($query)
    {
        $totalConvertedCount = (clone $query)
            ->count();

        $totalNewgenQrUaCount = (clone $query)
            ->where('source', 'NEWGEN')
            ->count();

        $totalFinnoneQrCount = (clone $query)
            ->where('source', 'FINNONE_QR')
            ->count();

        $totalFinnoneUaCount = (clone $query)
            ->where('source', 'FINNONE_UA')
            ->count();

        return [
            $totalConvertedCount,
            $totalNewgenQrUaCount,
            $totalFinnoneQrCount,
            $totalFinnoneUaCount
        ];
    }

    private function getRecentGenerations($query)
    {
        return (clone $query)
            ->take(10)
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $item->source,
                    'filename' => $item->converted_filename,
                    'timestamp' => $item->created_at
                ];
            });
    }

    private function getAuthorsPerGeneration($logQuery)
    {
        return (clone $logQuery)
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as total')
            ->where('module', 'Receipt Converter')
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                $user = $item->user;

                return [
                    'name' => trim(
                        $user->fname . " " . ($user->mname ?? '') . " " . $user->lname
                    ),
                    'count' => $item->total
                ];
            });
    }

    private function getGenerationLogs($query)
    {
        return (clone $query)
            ->select(
                DB::raw('DATE(created_at) AS date'),
                DB::raw('COUNT(DISTINCT converted_filename) AS count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count
                ];
            });
    }
}
