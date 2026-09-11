<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfflineSearchLmsRecord extends Model
{
    public function clientAccount(): BelongsTo
    {
        return $this->belongsTo(OfflineSearchClientAccount::class, 'clientacct_id');
    }

    public function losRecords(): HasMany
    {
        return $this->hasMany(OfflineSearchLosRecord::class, 'lms_record_id');
    }
}
