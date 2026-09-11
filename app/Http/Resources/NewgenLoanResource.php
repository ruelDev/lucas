<?php

namespace App\Http\Resources;

use App\Constants\LoanConstants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewgenLoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'agreementNumber' => $this->LOAN_NO,
            'misNumber'       => $this->MIS_NO,
            'customerName'    => optional($this->customersdetails)->customer_name,
            'aoc'             => $this->AOC,
            'arNumber'        => optional($this->instrumentdetails)->INSTRUMENT_ID,
            'arAmount'        => optional($this->instrumentdetails)->INSTRUMENT_AMOUNT,
            'paymentType'     => optional($this->instrumentdetails)->INSTRUMENT_MODE,
            'arDate'          => optional($this->instrumentdetails)->INSTRUMENT_DATE,
            'npaStage'        => $this->NPA_FLAG,
            'remarks'         => $this->LOAN_REMARKS,
            'source'          => $this->source,
            'recStatus'       => $this->REC_STATUS,
            'company'         => LoanConstants::getCompanyByPrefix(
                substr($this->LOAN_NO ?? '', 0, LoanConstants::MIN_PREFIX_LENGTH)
            ),
        ];
    }
}
