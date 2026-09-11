<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumentDetailsNewgenBmi extends Model
{
    protected $connection = 'sqlsrv_bmilmsdb';
    protected $table = 'cr_instrument_dtl';
    protected $primaryKey = 'INSTRUMENT_ID';
}
