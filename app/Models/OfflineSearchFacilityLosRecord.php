<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSearchFacilityLosRecord extends Model
{
    protected $fillable = [
        'financing_bank',
        'rlos_id',
        'status',
        'date_encoded',
        'decision_date',
        'remarks'
    ];

    public function lmsRecords()
    {
        return $this->hasMany(
            OfflineSearchFacilityLmsRecord::class,
            'loan_application_id',
            'rlos_id'
        );
    }
}
