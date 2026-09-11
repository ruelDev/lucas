<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressDetailsFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONEGCD.NBFC_ADDRESS_M';
    protected $primaryKey = 'bpid';
}
