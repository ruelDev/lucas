<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotorDetailsNewgenBfc extends Model
{
    protected $connection = 'sqlsrv_bfclmsdb';
    protected $table = 'dm_caset';
    protected $primaryKey = 'winame';
}
