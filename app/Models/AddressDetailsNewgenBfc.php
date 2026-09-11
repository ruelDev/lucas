<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressDetailsNewgenBfc extends Model
{
    protected $connection = 'sqlsrv_bfclmsdb';
    protected $table = 'com_address_m';
    protected $primaryKey = 'address_id';

}
