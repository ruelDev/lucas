<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDetailsNewgenBmi extends Model
{
    protected $connection ='sqlsrv_bmilmsdb';
    protected $table ='gcd_customer_m';
    protected $primaryKey = 'customer_id';
}
