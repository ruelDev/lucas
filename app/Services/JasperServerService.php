<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class JasperServerService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = config('jasper-report-client.jrs_base_url') . '/rest_v2/reports';
        $this->username = config('jasper-report-client.jrs_username');
        $this->password = config('jasper-report-client.jrs_password');
    }

    /**
     * Generate a PDF report from JasperServer
     *
     * @param string $reportPath The path to the report on JasperServer (e.g., '/Document_report/CFP')
     * @param array $params The parameters to pass to the report
     * @param string $logChannel The log channel to use
     * @return \Illuminate\Http\Response
     */
    public function generatePdfReport(string $reportPath, array $params, string $logChannel, string $filename = '')
    {
        try {
            $queryString = http_build_query($params);
            $reportUrl = $this->baseUrl . $reportPath . '.pdf' . ($queryString ? '?' . $queryString : '');

            Log::channel($logChannel)->info('Generating report from JasperServer', [
                'url' => $reportUrl,
                'params' => $params
            ]);

            // Make request to JasperServer
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(60)
                ->get($reportUrl);

            if ($response->successful()) {

                if ($filename != '') {
                    $generatedFilename = $filename . '.pdf';
                } else {
                    $generatedFilename = $this->generateFilename($reportPath);
                }

                Log::channel($logChannel)->info('Report generated successfully', [
                    'filename' => $generatedFilename
                ]);

                return response($response->body(), 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $generatedFilename . '"');
            }

            Log::channel($logChannel)->error('JasperServer request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to generate report from JasperServer',
                'status' => $response->status()
            ], 500);
        } catch (\Throwable $th) {
            Log::channel($logChannel)->error('Error generating report from JasperServer', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while generating the report',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a filename based on the report path
     *
     * @param string $reportPath
     * @return string
     */
    protected function generateFilename(string $reportPath): string
    {
        $reportName = basename($reportPath);
        return $reportName . '_Report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
    }

    /**
     * Get the full report URL with parameters
     *
     * @param string $reportPath
     * @param array $params
     * @param string $format
     * @return string
     */
    public function getReportUrl(string $reportPath, array $params = [], string $format = 'pdf'): string
    {
        $queryString = http_build_query($params);
        return $this->baseUrl . $reportPath . '.' . $format . ($queryString ? '?' . $queryString : '');
    }

    public function generateCsvReport(string $reportPath, array $params, string $logChannel)
    {
        try {
            $queryString = http_build_query($params);

            $reportUrl = $this->baseUrl
                . $reportPath
                . '.csv'
                . ($queryString ? '?' . $queryString : '');

            Log::channel($logChannel)->info('Generating CSV report from JasperServer', [
                'url' => $reportUrl,
                'params' => $params
            ]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(60)
                ->get($reportUrl);

            if ($response->successful()) {
                $filename = $this->generateFilenameCsv($reportPath);

                return response($response->body(), 200)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }

            Log::channel($logChannel)->error('JasperServer CSV request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to generate CSV report from JasperServer',
                'status' => $response->status()
            ], 500);
        } catch (\Throwable $th) {
            Log::channel($logChannel)->error('Error generating CSV report', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while generating the CSV report',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    protected function generateFilenameCsv(string $reportPath): string
    {
        $reportName = basename($reportPath);

        return $reportName
            . '_Report_'
            . now()->format('Y-m-d_H-i-s')
            . '.csv';
    }

    public function generateExcelReport(string $reportPath, array $params, string $logChannel, string $filename = '')
    {
        try {
            $extension = '.xlsx';

            $queryString = http_build_query($params);

            $reportUrl = $this->baseUrl
                . $reportPath
                . $extension
                . ($queryString ? '?' . $queryString : '');

            Log::channel($logChannel)->info('Generating Excel report from JasperServer', [
                'url' => $reportUrl,
                'params' => $params
            ]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(60)
                ->get($reportUrl);

            if ($response->successful()) {

                if ($filename != '') {
                    $generatedFilename = $filename . $extension;
                } else {
                    $generatedFilename = $this->generateFilenameExcel($reportPath);
                }

                return response($response->body(), 200)
                    ->header('Content-Type', 'application/vnd.ms-excel')
                    ->header('Content-Disposition', 'attachment; filename="' . $generatedFilename . '"');
            }

            Log::channel($logChannel)->error('JasperServer Excel request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to generate Excel report from JasperServer',
                'status' => $response->status()
            ], 500);
        } catch (\Throwable $th) {
            Log::channel($logChannel)->error('Error generating Excel report', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while generating the Excel report',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    protected function generateFilenameExcel(string $reportPath): string
    {
        $reportName = basename($reportPath);

        return $reportName
            . '_Report_'
            . now()->format('Y-m-d_H-i-s')
            . '.xlsx';
    }
}
