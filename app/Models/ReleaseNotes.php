<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseNotes extends Model
{
    protected $fillable = [
        'app_version_id',
        'category',
        'title',
        'description',
        'sort_order',
    ];

    public function appVersion() {
        return $this->belongsTo(AppVersion::class);
    }
}
