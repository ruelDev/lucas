<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanDetailsFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONELEA.LEA_AGREEMENT_DTL';
    protected $primaryKey = 'agreementid';


    public function customerdetailsfinnone(): BelongsTo
    {
        return $this->belongsTo(CustomerDetailsFinnone::class, 'lesseeid', 'customerid');
    }

    public function addressdetailsfinnone(): BelongsTo
    {
        return $this->belongsTo(AddressDetailsFinnone::class, 'lesseeid', 'bpid');
    }

    public function assetdetailsfinnone(): BelongsTo
    {
        return $this->belongsTo(AssetsDetailsMFinnone::class, 'agreementid', 'agreementid');
    }
 
    public function mailingaddress(): BelongsTo
    {
        return $this->belongsTo(AddressDetailsFinnone::class, 'lesseeid', 'bpid')
                    ->where('mailingaddress', 'Y');
    }

    public function accountdetails(): BelongsTo
    {
        return $this->belongsTo(AccountDetailsFinnone::class, 'agreementno', 'agreementno');
    }

    public function chequedetails(): BelongsTo
    {
        return $this->belongsTo(ChequeDetailsFinnone::class, 'agreementid', 'reference');
    }
}
