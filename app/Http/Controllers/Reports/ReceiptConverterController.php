<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ReceiptConverterLog;
use App\Services\Reports\ReceiptConverter\ReceiptConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptConverterController extends Controller
{
    protected $receiptConverterService;

    public function __construct(ReceiptConverterService $receiptConverterService)
    {
        $this->receiptConverterService = $receiptConverterService;
    }

    public function converter()
    {
        return inertia('reports/receipt-converter/index');
    }

    public function bulkUpload(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xls,xlsx|max:5000',
            ]);

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            // for checking on whether the filename is currently existing
            $filename = $file->getClientOriginalName();

            $existingFilenames = ReceiptConverterLog::where('old_filename', $filename)->first();

            if ($existingFilenames) {
                return response()->json([
                    'error' => 'File already converted once',
                ], 500);
            }

            $tempPath = $file->store('temp', 'local');
            $fullPath = Storage::disk('local')->path($tempPath);

            [$rows, $summary, $source, $returnHeader] = $this->receiptConverterService->processFile($fullPath, $extension, $file);

            Storage::disk('local')->delete($tempPath);

            return response()->json([
                'rows' => $rows,
                'summary' => $summary,
                'source' => $source,
                'returnHeader' => $returnHeader,
            ]);
        } catch (\Throwable $th) {
            Log::channel('receipt_converter')->info('Bulk upload error: ' . $th->getMessage());
            return response()->json([
                'error' => 'Failed to process file: ' . $th->getMessage()
            ], 500);
        }
    }

    public function bulkConvert(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '1G');

        try {
            $source = $request->input('source');
            $category = $request->input('type');
            $originalFilename = $request->input('originalFilename');
            $collections = $request->rows;

            if (empty($collections)) {
                return response()->json([
                    'error' => 'No data to export'
                ], 400);
            }

            return $this->receiptConverterService->exportToFile($collections, $source, $category, $originalFilename);
        } catch (\Throwable $th) {
            Log::channel('receipt_converter')->error('Export to Excel error: ' . $th->getMessage());
            Log::channel('receipt_converter')->error($th->getTraceAsString());

            return response()->json([
                'error' => 'Failed to export file: ' . $th->getMessage()
            ], 500);
        }
    }
}
