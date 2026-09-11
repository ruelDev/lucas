<?php

namespace App\Http\Resources;

use App\Constants\LoanConstants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinnoneLoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'agreementNumber' => $this->agreementno,
            'misNumber'       => $this->fileno,
            'customerName'    => optional($this->customerdetailsfinnone)->customername,
            'aoc'             => optional($this->accountdetails)->aoc,
            'arNumber'        => optional($this->accountdetails)->last_receipt_no,
            'arAmount'        => optional($this->accountdetails)->last_receipt_amount,
            'paymentType'     => optional($this->chequedetails)->chequetype,
            'arDate'          => optional($this->accountdetails)->last_receipt_date,
            'remarks'         => optional($this->chequedetails)->remarks,
            'npaStage'        => $this->npa_stageid,
            'source'          => $this->source,
            'recStatus'       => $this->status,
            'company'         => LoanConstants::getCompanyBySchemeId((string) $this->schemeid),
        ];
    }
}
