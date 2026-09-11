<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotorDetailsNewgenBmi extends Model
{
    //
    protected $connection = 'sqlsrv_bmilmsdb';
    protected $table = 'dm_caset';
    protected $primaryKey = 'winame';
}
