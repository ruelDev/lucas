<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordArchive extends Model
{
    protected $table = 'password_archives';

    protected $fillable = [
        'user_id',
        'last_effective_date',
        'password'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
