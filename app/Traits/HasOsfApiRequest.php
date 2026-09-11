<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait HasOsfApiRequest
{

    private const CUSTOMER_DATA = '/customer-data';
    private const LOAN_APPLICATION_DETAILS = '/loan-application-details';
    private const LOAN_DETAILS = '/loan-details';
    private const PAYMENT = '/payment';

    private function getBaseUrl()
    {
        return config('newgen-db-api.base_url');
    }

    private function buildHttpRequest($url, $page, $params = [])
    {
        return Http::withoutVerifying()
            ->timeout(300)
            ->retry(3, 5000)
            ->get($url, array_merge([
                'page' => $page,
                'limit' => 1000
            ], $params));
    }

    /**
     *  For ETL Processes
     */
    public function apiCustomerData($page)
    {
        $url = $this->getBaseUrl() . self::CUSTOMER_DATA;

        return $this->buildHttpRequest($url, $page);
    }

    public function apiLosRecordData($page)
    {
        $url = $this->getBaseUrl() . self::LOAN_APPLICATION_DETAILS;

        return $this->buildHttpRequest($url, $page);
    }

    public function apiLmsRecordData($page)
    {
        $url = $this->getBaseUrl() . self::LOAN_DETAILS;

        return $this->buildHttpRequest($url, $page);
    }

    public function apiPaymentData($page)
    {
        $url = $this->getBaseUrl() . self::PAYMENT;

        return $this->buildHttpRequest($url, $page);
    }

    /**
     * For Direct Searching Processes
     */
    public function apiSearchCustomerData($page, $customer)
    {
        $name = implode(
                " ",
                array_filter(
                    array_map('trim', [
                        $customer['firstName'] ?? '',
                        $customer['middleName'] ?? '',
                        $customer['lastName'] ?? '',
                    ])
                ));

        $params = [
            'filter[CUSTOMER_NAME]' => $name
        ];

        $url = $this->getBaseUrl() . self::CUSTOMER_DATA;

        return $this->buildHttpRequest($url, $page, $params);
    }

    public function apiSearchCustomerDetailsData($page, $customerID)
    {
        $params = [
            'filter[CUSTOMER_ID]' => $customerID,
        ];

        $url = $this->getBaseUrl() . self::CUSTOMER_DATA;

        return $this->buildHttpRequest($url, $page, $params);
    }

    public function apiSearchCustomerLosData($page, $loan_id)
    {
        $params = [
            'filter[TID]' => $loan_id,
        ];

        $url = $this->getBaseUrl() . self::LOAN_APPLICATION_DETAILS;

        return $this->buildHttpRequest($url, $page, $params);
    }

    public function apiSearchCustomerLmsData($page, $customer)
    {
        $params = [
            'filter[CUSTOMER_ID]' => $customer,
        ];

        $url = $this->getBaseUrl() . self::LOAN_DETAILS;

        return $this->buildHttpRequest($url, $page, $params);
    }

    public function apiSearchPaymentData($page, $agreementID)
    {
        $params = [
            'filter[LOAN_ID]' => $agreementID,
        ];

        $url = $this->getBaseUrl() . self::PAYMENT;

        $response = $this->buildHttpRequest($url, $page, $params);
        $paymentData = json_decode($response->body())->data;
        
        if (empty($paymentData)) {
            return null;
        }

        $latest = collect($paymentData)->sortByDesc('PAYMENT_DATE')->first();

        return $latest->PAYMENT_DATE ?? null;
    }
}
