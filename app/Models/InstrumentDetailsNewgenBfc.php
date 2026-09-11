<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumentDetailsNewgenBfc extends Model
{
    protected $connection = 'sqlsrv_bfclmsdb';
    protected $table = 'cr_instrument_dtl';
    protected $primaryKey = 'INSTRUMENT_ID';
}
