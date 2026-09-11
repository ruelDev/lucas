<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtlProcessLog extends Model
{
    protected $fillable = [
        'process_name',
        'last_page',
        'total_processed',
        'last_run_at'
    ];

    protected $casts = [
        'last_page'             => 'integer',
        'total_processed'       => 'integer',
        'last_run_at'           => 'datetime'
    ];
}
