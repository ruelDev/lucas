<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status',
        'bank_id',
        'makerId',
        'makerName',
        'depositSlip',
        'depositDate',
        'depositAmount',
        'depositCharge',
        'referenceNumber',
        'depositoryRemarks',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function banks(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
