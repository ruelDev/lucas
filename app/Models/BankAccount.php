<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_id',
        'account_number',
        'depository_remarks',
    ];

    public function banks(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
