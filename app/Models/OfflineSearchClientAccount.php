<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OfflineSearchClientAccount extends Model
{
    public function customerDetails(): BelongsTo
    {
        return $this->belongsTo(OfflineSearchClientRecord::class, 'client_id');
    }

    public function lmsRecords(): HasMany
    {
        return $this->hasMany(OfflineSearchLmsRecord::class, 'clientacct_id');
    }

    public function losRecords(): HasManyThrough
    {
        return $this->hasManyThrough(
            OfflineSearchLosRecord::class,
            OfflineSearchLmsRecord::class,
            'clientacct_id',
            'lms_record_id',
            'id',
            'id'
        );
    }
}
