<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanDetailsNewgenBmi extends Model
{
    protected $connection = 'sqlsrv_bmilmsdb';
    protected $table = 'cr_loan_dtl';
    protected $primaryKey = 'loan_no';

    public function customersdetails(): BelongsTo
    {
        return $this->belongsTo(CustomerDetailsNewgenBmi::class, 'LOAN_CUSTOMER_ID', 'customer_id');
    }

    public function motordetails(): BelongsTo
    {
        return $this->belongsTo(MotorDetailsNewgenBmi::class, 'LOAN_REFERENCE_NO', 'WINAME');
    }

    public function addressdetails(): BelongsTo
    {
        return $this->belongsTo(AddressDetailsNewgenBmi::class, 'LOAN_CUSTOMER_ID', 'BPID');
    }

    public function instrumentdetails(): BelongsTo
    {
        return $this->belongsTo(InstrumentDetailsNewgenBmi::class, 'LOAN_ID', 'TXNID');
    }
}
