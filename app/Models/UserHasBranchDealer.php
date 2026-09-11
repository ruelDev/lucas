<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserHasBranchDealer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'type',
        'branch_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'branch_id', 'id');
    }

    public function assignable()
    {
        if ($this->type === 'BRANCH') {
            return $this->branch();
        }

        if ($this->type === 'DEALER') {
            return $this->dealer();
        }

        return null;
    }

    public function getAssignableAttribute()
    {
        if ($this->type === 'BRANCH') {
            return $this->branch;
        }

        if ($this->type === 'DEALER') {
            return $this->dealer;
        }

        return null;
    }
}
