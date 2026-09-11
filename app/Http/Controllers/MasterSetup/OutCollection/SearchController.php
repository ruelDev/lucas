<?php

namespace App\Http\Controllers\MasterSetup\OutCollection;

use App\Constants\LoanConstants;
use App\Http\Controllers\Controller;
use App\Services\LoanSearchService;
use App\Traits\HasAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    use HasAuditLog;

    protected $module = 'Out Collection - Receipt Posting';

    public function __construct(private readonly LoanSearchService $searchService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'searchBy' => ['required', 'string'],
                'value'    => ['required', 'string', 'min:3'],
            ]);

            $results  = $validated['searchBy'] === LoanConstants::SEARCH_BY_AGREEMENT
                ? $this->searchService->searchByAgreementNo($validated['value'])
                : $this->searchService->searchByMisNumber($validated['value']);


            $resolved = collect($results->resolve());

            $action = $validated['searchBy'] === LoanConstants::SEARCH_BY_AGREEMENT
                ? 'SEARCH: AGREEMENT NUMBER'
                : 'SEARCH: MIS NUMBER';

            $this->auditLogs($action, $this->module);

            return $this->buildResponse($resolved) ?? response()->json([
                'results' => $resolved->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ]);
        } catch (\Throwable $th) {
            $hasErrorCode = $th->getCode() === '08001' || $th->getCode() === 0;
            $message = $hasErrorCode
                ? 'Database connection unavailable. Please contact the System Administration Department - Local Number: 2617.'
                : 'An unexpected error occurred.';

            return response()->json([
                'message'      => $message,
                'hasErrorCode' => $hasErrorCode,
            ]);
        }
    }

    private function buildResponse(\Illuminate\Support\Collection $resolved): ?JsonResponse
    {
        $closedLoan = $resolved->first(
            fn($item) => strtoupper($item['recStatus'] ?? '') === 'C'
        );

        if ($closedLoan) {
            $status = match (strtoupper($closedLoan['recStatus'])) {
                'C'     => 'CLOSED',
                default => $closedLoan['recStatus'],
            };

            return response()->json([
                'status'  => $status,
                'results' => $resolved->values(),
                'message' => "Account entered is in '{$status}' status!",
            ]);
        }

        $invalidNpa = $resolved->first(
            fn($item) => in_array(strtoupper($item['npaStage'] ?? ''), LoanConstants::NPA_DISALLOWED)
        );

        if ($invalidNpa) {
            return response()->json([
                'npaStage' => $invalidNpa['npaStage'],
                'results'  => $resolved->values(),
                'message'  => "Account entered is in '{$invalidNpa['npaStage']}' stage!",
            ]);
        }

        return null;
    }
}
