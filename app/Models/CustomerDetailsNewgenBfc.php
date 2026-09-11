<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDetailsNewgenBfc extends Model
{
    protected $connection ='sqlsrv_bfclmsdb';
    protected $table ='gcd_customer_m';
    protected $primaryKey = 'customer_id';
}
