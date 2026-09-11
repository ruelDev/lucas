<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionVersion extends Model
{
    protected $fillable = [
        'user_id',
        'session_version'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
