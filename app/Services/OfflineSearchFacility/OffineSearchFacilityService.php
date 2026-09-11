<?php

namespace App\Services\OfflineSearchFacility;

use App\Traits\HasOsfApiRequest;
use Carbon\Carbon;

class OffineSearchFacilityService
{
    use HasOsfApiRequest;

    protected $defaultPage = 1;

    private function handleCustomerData($customer)
    {
        return [
            'CUSTOMER_ID' => $customer->CUSTOMER_ID,
            'FIRST_NAME' => $customer->FIRST_NAME,
            'MIDDLE_NAME' => $customer->MIDDLE_NAME,
            'LAST_NAME' => $customer->LAST_NAME,
            'DATE_OF_BIRTH' => $customer->DATE_OF_BIRTH,
            'ADDRESS1' => $customer->ADDRESS1
        ];
    }

    public function searchCustomerData($customer)
    {
        $response = $this->apiSearchCustomerData($this->defaultPage, $customer);
        $searchResults = json_decode($response->body())->data;

        return collect($searchResults)
            ->map(fn($customer) => $this->handleCustomerData($customer))
            ->values();
    }

    public function searchCustomerDetailsData($customerID)
    {
        $response = $this->apiSearchCustomerDetailsData($this->defaultPage, $customerID);
        $searchResult = json_decode($response->body())->data;

        return collect($searchResult)
            ->map(fn($customer) => $this->handleCustomerData($customer))
            ->first();
    }

    private function handleLosData($losRecord)
    {
        return [
            'FINANCING_BANK' => $losRecord->SOURCE,
            'RLOS_ID' => $losRecord->TID ?? null,
            'STATUS' => $losRecord->STATUS ?? null,
            'DATE_ENCODED' => !empty($losRecord->DATE_ENCODED)
                ? Carbon::parse($losRecord->DATE_ENCODED)->format('Y-m-d')
                : null,
            'DECISION_DATE' => !empty($losRecord->DATE_DECISION)
                ? Carbon::parse($losRecord->DATE_DECISION)->format('Y-m-d')
                : null,
            'REMARKS' => $losRecord->REMARKS ?? null,
        ];
    }

    public function searchCustomerLosData($lmsData)
    {
        $losData = [];

        foreach ($lmsData as $lms) {
            $response = $this->apiSearchCustomerLosData($this->defaultPage, $lms);
            $los = json_decode($response->body())->data;

            foreach ($los as $record) {
                $losData[] = $this->handleLosData($record);
            }
        }
        
        return $losData;
    }

    private function handleLmsData($lmsRecord)
    {
        return [
            'LOAN_APPLICATION_ID' => $lmsRecord->LOAN_APPLICATION_ID ?? null,
            'AGREEMENT_NO' => $lmsRecord->AGREEMENT_NO ?? null,
            'MIS_NO' => $lmsRecord->MIS_NO ?? null,
            'AGREEMENT_ID' => $lmsRecord->AGREEMENT_ID ?? null,
            'DATE_SOLD' => $lmsRecord->DATE_SOLD ?? null,
            'FIRST_DUE_DATE' => $lmsRecord->REPAYMENT_STARTDATE ?? null,
            'MATURITY_DATE' => $lmsRecord->MATURITY_DATE ?? null,
            'LAST_PAYMENT_DATE' => !empty($lmsRecord->LAST_PAYMENT_DATE)
                ? Carbon::parse($lmsRecord->LAST_PAYMENT_DATE)->format('Y-m-d')
                : null,
            'LOAN_AMOUNT' => $lmsRecord->AMOUNT_FINANCE ?? null,
            'LOAN_TERM' => $lmsRecord->LOAN_TENURE ?? null,
            'EMI' => $lmsRecord->EMI ?? null,
            'LOAN_STATUS' => $lmsRecord->LOAN_STATUS ?? null,
            'NPA_STAGEID' => $lmsRecord->NPA_STAGED ?? null,
            'ACCOUNT_RATING' => $lmsRecord->ACCOUNT_RATING ?? null,
        ];
    }

    public function searchCustomerLmsData($customer)
    {
        $response = $this->apiSearchCustomerLmsData($this->defaultPage, $customer);
        $lmsData = json_decode($response->body())->data;

        foreach ($lmsData as $lms) {
            $lms->LAST_PAYMENT_DATE = $this->apiSearchPaymentData($this->defaultPage, $lms->AGREEMENT_ID);
        }

        return collect($lmsData)
            ->map(fn($lms) => $this->handleLmsData($lms))
            ->values();
    }
}
