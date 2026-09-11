<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptConverterLog extends Model
{
    protected $fillable = [
        'source',
        'old_filename',
        'converted_filename',
        'series',
        'date',
        'remarks',
        'created_at',
        'updated_at',
    ];
}
