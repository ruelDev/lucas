<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'release_type',
        'released_at'
    ];

    public function releaseNotes() {
        return $this->hasMany(ReleaseNotes::class);
    }
}
