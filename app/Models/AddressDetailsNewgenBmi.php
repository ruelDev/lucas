<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressDetailsNewgenBmi extends Model
{
    protected $connection = 'sqlsrv_bmilmsdb';
    protected $table = 'com_address_m';
    protected $primaryKey = 'address_id';

}
