<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDetailsFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONELEA.ACCOUNT_DETAILS';
    protected $primaryKey = 'ACCOUNTNO';
}
