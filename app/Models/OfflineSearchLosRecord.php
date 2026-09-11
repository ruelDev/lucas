<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineSearchLosRecord extends Model
{
    public function lmsRecord(): BelongsTo
    {
        return $this->belongsTo(OfflineSearchLmsRecord::class, 'lms_record_id');
    }
}
