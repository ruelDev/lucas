<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PHPJasper\PHPJasper;
use Symfony\Component\Process\Process;

class JasperReportService
{
    protected $defaultRedirectStderr2 = ' 2>&1';

    public function checkJrxml($jrxmlFile)
    {
        Log::channel('jasper_reports')->info('=== Starting Step 1: Checking JRXML ===');
        if (!file_exists($jrxmlFile)) {
            Log::channel('jasper_reports')->error('JRXML File not found: ' . $jrxmlFile);

            return [
                'success' => false,
                'message' => 'JRXML file not found'
            ];
        }

        Log::channel('jasper_reports')->info('=== Finished Step 1: Checking JRXML ===');
        return [
            'success' => true,
            'message' => 'JRXML file found'
        ];
    }

    public function preCheckJasper($jasperFile)
    {
        if (file_exists($jasperFile)) {
            unlink($jasperFile);
            Log::channel('jasper_reports')->info('Removed existing jasper file');
        }
    }

    public function postCheckJasper($jasperFile)
    {
        if (!file_exists($jasperFile)) {
            return [
                'success' => false,
                'message' => 'JRXML compilaton failed - Jasper file not created'
            ];
        }
        return [
            'success' => true,
            'message' => 'JRXML File compiled successfully',
        ];
    }

    public function compileJrxml($jrxmlFile, $reportsDir, $jasperFile, $jasperFileName, $jasperStarterPath)
    {
        Log::channel('jasper_reports')->info('=== Starting Step 2: Compiling JRXML to Jasper ===');

        $jasper = new PHPJasper;

        $this->preCheckJasper($jasperFile);

        $compileOptions = [
            'executable' => $jasperStarterPath,
        ];

        try {
            $compileCommand = $jasper->compile($jrxmlFile, $reportsDir . DIRECTORY_SEPARATOR . $jasperFileName, $compileOptions)->output();
            Log::channel('jasper_reports')->info('Original Compile command: ' . $compileCommand);

            if (strpos($compileCommand, $jasperStarterPath) === false) {
                Log::channel('jasper_reports')->info('Expected path: ' . $jasperStarterPath);
                $compileCommand = str_replace('jasperstarter', '"' . $jasperStarterPath . '"', $compileCommand);
                Log::channel('jasper_reports')->info('Modified compile command: ' . $compileCommand);
            }

            $compileCommand = 'cd ' . escapeshellarg(dirname($jasperStarterPath)) . ' && ' . $compileCommand . $this->defaultRedirectStderr2;
            $output = shell_exec($compileCommand);

            $compileLog = $output ?: 'No compile output';
            $compileError = $output && strpos($output, 'ERROR') !== false ? $output : 'No compile errors';

            Log::channel('jasper_reports')->info('Shell exec compile output: ' . $compileLog);

            if (!empty($compileError)) {
                Log::channel('jasper_reports')->error('Compile errors: ' . $compileError);
            }
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Failed to compile JRXML into Jasper File'
            ];
        }

        $postCheck = $this->postCheckJasper($jasperFile);

        if (!$postCheck['success']) {
            return [
                'success' => false,
                'message' => $postCheck['message']
            ];
        }

        Log::channel('jasper_reports')->info('✓ JRXML compiled successfully');
        Log::channel('jasper_reports')->info('=== Finished Step 2: Compiling JRXML to Jasper ===');
        return [
            'success' => true,
            'message' => 'JRXML compiled to Jasper File successfully',
        ];
    }

    public function convertIntoParamString($params)
    {
        $paramString = '';
        if (!empty($params)) {
            $pairs = [];
            foreach ($params as $key => $value) {
                $pairs[] = $key . '=' . escapeshellarg($value);
            }
            $paramString = ' -P ' . implode(' ', $pairs);
        }
        return $paramString;
    }

    public function checkPdfOutput($outputPath)
    {
        $pdfFile = $outputPath . '.pdf';
        if (!file_exists($pdfFile)) {
            return [
                'success' => false,
                'message' => 'PDF File was not found',
            ];
        }
        Log::channel('jasper_reports')->info('✓ PDF generated successfully at: ' . $pdfFile);
        Log::channel('jasper_reports')->info('PDF file size: ' . filesize($pdfFile) . ' bytes');
        return [
            'success' => true,
            'message' => 'PDF File found at: ' . $outputPath,
        ];
    }

    public function generate($params, $jasperStarterPath, $jdbc_dir, $jasperFile, $outputPath, $options)
    {
        Log::channel('jasper_reports')->info('=== Starting Step 3: Generating PDF from Jasper ===');

        $jasper = new PHPJasper;

        $paramString = $this->convertIntoParamString($params);

        try {
            $processCommand = $jasper->process($jasperFile, $outputPath, $options)->output();
            Log::channel('jasper_reports')->info('Original process command: ' . $processCommand);

            if (strpos($processCommand, $jasperStarterPath) === false) {
                Log::channel('jasper_reports')->info('Expected path: ' . $jasperStarterPath);
                $processCommand = str_replace('jasperstarter', '"' . $jasperStarterPath . '"', $processCommand);
                Log::channel('jasper_reports')->info('Modified process command: ' . $processCommand);
            }

            if ($options['db_connection']['host'] === 'none') {
                $processCommand = 'cd ' . escapeshellarg(dirname($jasperStarterPath)) . ' && "' . $jasperStarterPath . '" --locale en process '
                    . escapeshellarg($jasperFile) .
                    ' -o ' . escapeshellarg($outputPath) .
                    ' -f pdf ' .
                    $paramString . $this->defaultRedirectStderr2;
            } else {
                $processCommand = 'cd ' . escapeshellarg(dirname($jasperStarterPath)) . ' && "' . $jasperStarterPath . '" --locale en process '
                    . escapeshellarg($jasperFile) .
                    ' -o ' . escapeshellarg($outputPath) .
                    ' -f pdf -t generic' .
                    ' -H ' . escapeshellarg($options['db_connection']['host']) .
                    ' --db-port ' . escapeshellarg($options['db_connection']['port']) .
                    ' -n ' . escapeshellarg($options['db_connection']['database']) .
                    ' -u ' . escapeshellarg($options['db_connection']['username']) .
                    ' -p ' . escapeshellarg($options['db_connection']['password']) .
                    ' --db-driver ' . escapeshellarg($options['db_connection']['jdbc_driver']) .
                    ' --db-url ' . escapeshellarg($options['db_connection']['jdbc_url']) .
                    ' --jdbc-dir "' . $jdbc_dir . '"' .
                    $paramString . $this->defaultRedirectStderr2;
            }

            Log::channel('jasper_reports')->info($processCommand);
            $output = shell_exec($processCommand);
            $processLog = $output ?: 'No process output';
            $processError = $output && strpos($output, 'ERROR') !== false ? $output : 'No process errors';

            Log::channel('jasper_reports')->info('Shell exec process output: ' . $processLog);

            if (!empty($processError)) {
                Log::channel('jasper_reports')->error('Process errors: ' . $processError);
            }

            $checkPdfOutput = $this->checkPdfOutput($outputPath);
            if (!$checkPdfOutput['success']) {
                return [
                    'success' => false,
                    'message' => $checkPdfOutput['message'],
                ];
            }

            return [
                'success' => true,
                'message' => 'Report generated successfully.'
            ];
        } catch (\Throwable $th) {
            Log::channel('jasper_reports')->error('Error: ' . $th->getMessage());
            Log::channel('jasper_reports')->error('File: ' . $th->getFile() . ':' . $th->getLine());
            Log::channel('jasper_reports')->error('Stack trace: ' . $th->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Report generation failed',
            ];
        }
    }


    public function streamPdf($filename, $outputPath)
    {
        $filename = $filename . ".pdf";
        $outputPath = $outputPath . ".pdf";

        Log::channel('jasper_reports')->info('Opening File' . $filename . ' at ' . $outputPath);
        if (!file_exists($outputPath)) {
            abort(404, 'File not found');
        }

        return response()->file($outputPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}
