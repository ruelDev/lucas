<?php

namespace App\Services;

use App\Constants\LoanConstants;
use App\Models\LoanDetailsFinnone;
use App\Models\LoanDetailsNewgenBfc;
use App\Models\LoanDetailsNewgenBmi;
use App\Http\Resources\FinnoneLoanResource;
use App\Http\Resources\NewgenLoanResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoanSearchService
{
    public function searchByAgreementNo(string $value): AnonymousResourceCollection
    {
        $prefix = strtoupper(substr($value, 0, LoanConstants::MIN_PREFIX_LENGTH));

        if (strlen($value) < LoanConstants::MIN_PREFIX_LENGTH) {
            return NewgenLoanResource::collection(collect());
        }

        return match (true) {
            str_starts_with($value, LoanConstants::PREFIX_BMI),
            str_starts_with($value, LoanConstants::PREFIX_BFC) => $this->searchNewgenByAgreementNo($prefix, $value),
            default                                            => $this->searchFinnoneByAgreementNo($value),
        };
    }

    public function searchByMisNumber(string $value): AnonymousResourceCollection
    {
        $bmiResults = $this->queryNewgen(LoanDetailsNewgenBmi::class, 'MIS_NO', $value);
        if ($bmiResults->isNotEmpty()) {
            return NewgenLoanResource::collection($bmiResults);
        }

        $bfcResults = $this->queryNewgen(LoanDetailsNewgenBfc::class, 'MIS_NO', $value);
        if ($bfcResults->isNotEmpty()) {
            return NewgenLoanResource::collection($bfcResults);
        }

        return FinnoneLoanResource::collection(
            LoanDetailsFinnone::with([
                'accountdetails',
                'chequedetails',
                'customerdetailsfinnone',
                'addressdetailsfinnone',
            ])
                ->where('fileno', $value)
                ->get()
                ->each(fn($item) => $item->source = 'finnone')
        );
    }

    // --- Private Helpers ---

    private function searchNewgenByAgreementNo(string $prefix, string $value): AnonymousResourceCollection
    {
        $model = $prefix === LoanConstants::PREFIX_BMI
            ? LoanDetailsNewgenBmi::class
            : LoanDetailsNewgenBfc::class;

        return NewgenLoanResource::collection(
            $model::with(['customersdetails', 'instrumentdetails'])
                ->where('LOAN_NO', $value)
                ->get()
                ->each(fn($item) => $item->source = 'newgen')
        );
    }

    private function searchFinnoneByAgreementNo(string $value): AnonymousResourceCollection
    {
        return FinnoneLoanResource::collection(
            LoanDetailsFinnone::with([
                'accountdetails',
                'chequedetails',
                'customerdetailsfinnone',
                'addressdetailsfinnone',
            ])
                ->where('agreementno', $value)
                ->get()
                ->each(fn($item) => $item->source = 'finnone')
        );
    }

    private function queryNewgen(string $model, string $column, string $value)
    {
        return $model::with(['customersdetails', 'instrumentdetails'])
            ->where($column, $value)
            ->get()
            ->each(fn($item) => $item->source = 'newgen');
    }
}
