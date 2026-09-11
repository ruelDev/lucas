<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDetailsFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONEGCD.NBFC_CUSTOMER_M';
    protected $primaryKey = 'customerid';
}
