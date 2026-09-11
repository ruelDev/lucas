<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSearchFacilityPaymentRecord extends Model
{
    protected $fillable = [
        'loan_id',
        'payment_type',
        'dealing_bank_id',
        'payment_mode',
        'receipt_no',
        'payment_amount',
        'pdc_flag',
        'status',
        'payment_date',
        'deposit_date',
        'bp_type',
        'bp_id',
        'remarks',
        'branch_id'
    ];

    public function lmsRecord()
    {
        return $this->belongsTo(
            OfflineSearchFacilityLmsRecord::class,
            'loan_id',
            'agreement_id'
        );
    }
}
