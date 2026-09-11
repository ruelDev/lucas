<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $connection = 'sqlsrv_bminlsdb';
    protected $table = 'doc_print_signatory';
    protected $primaryKey = 'code';
}
