<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSearchFacilityCustomer extends Model
{
    protected $fillable = [
        'customer_id',
        'fname',
        'mname',
        'lname',
        'dob',
        'address'
    ];
    
    public function fullName()
    {
        return $this->fname . ' ' .
            ($this->mname ? $this->mname . ' ' : '') .
            $this->lname;
    }

    public function lmsRecord()
    {
        return $this->hasMany(
            OfflineSearchFacilityLmsRecord::class,
            'customer_id',
            'customer_id'
        );
    }
}
