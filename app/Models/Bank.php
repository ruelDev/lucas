<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'abbreviation',
        'description',
    ];

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }
}
