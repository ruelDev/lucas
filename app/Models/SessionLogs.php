<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLogs extends Model
{
    protected $fillable = [
        'user_id',
        'session_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
