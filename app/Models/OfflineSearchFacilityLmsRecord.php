<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSearchFacilityLmsRecord extends Model
{
    protected $fillable = [
        'customer_id',
        'loan_application_id',
        'agreement_no',
        'account_number',
        'agreement_id',
        'date_sold',
        'first_due_date',
        'maturity_date',
        'loan_amount',
        'loan_term',
        'emi',
        'loan_status',
        'npa_stage',
        'account_rating'
    ];

    public function customer()
    {
        return $this->belongsTo(
            OfflineSearchFacilityCustomer::class,
            'customer_id',
            'customer_id'
        );
    }

    public function loanApplication()
    {
        return $this->belongsTo(
            OfflineSearchFacilityLosRecord::class,
            'loan_application_id',
            'rlos_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            OfflineSearchFacilityPaymentRecord::class,
            'loan_id',
            'agreement_id'
        );
    }
}
