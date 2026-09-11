<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeDetailsFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONELEA.NBFC_CHEQUE_DTL';
    protected $primaryKey = 'CHEQUEID';
}
