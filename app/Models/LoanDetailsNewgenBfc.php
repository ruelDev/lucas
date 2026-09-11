<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class LoanDetailsNewgenBfc extends Model
{
    protected $connection = 'sqlsrv_bfclmsdb';
    protected $table = 'cr_loan_dtl';
    protected $primaryKey = 'loan_no';

    public function customersdetails():BelongsTo
    {
        return $this->belongsTo(CustomerDetailsNewgenBfc::class, 'LOAN_CUSTOMER_ID','customer_id');
    }

    public function motordetails():BelongsTo
    {
        return $this->belongsTo(MotorDetailsNewgenBfc::class, 'LOAN_REFERENCE_NO','WINAME');
    }

    public function addressdetails():BelongsTo
    {
        return $this->belongsTo(AddressDetailsNewgenBfc::class, 'LOAN_CUSTOMER_ID','BPID');
    }

    public function instrumentdetails(): BelongsTo
    {
        return $this->belongsTo(InstrumentDetailsNewgenBfc::class, 'LOAN_ID', 'TXNID');
    }
}
