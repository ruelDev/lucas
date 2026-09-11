<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'deposit_id',
        'makerId',
        'makerName',
        'agreementNumber',
        'referenceNumber',
        'misNumber',
        'customerName',
        'aoc',
        'arDate',
        'arAmount',
        'arNumber',
        'paymentType',
        'npaStage',
        'reason',
        'status',
        'source',
        'remarks',
        'receiptType',
        'company',
        'authorizerId',
        'dateAuthor',
    ];

    public function deposits(): BelongsTo
    {
        return $this->belongsTo(Deposit::class, 'deposit_id');
    }
}
