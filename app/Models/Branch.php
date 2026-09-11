<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'branch_code',
        'location',
        'status',
        'remarks'
    ];

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('branch_code', 'like', "%{$value}%")
            ->orWhere('location', 'like', "%{$value}%");
    }

        public function userAssignments(): HasMany
    {
        return $this->hasMany(UserHasBranchDealer::class, 'branch_id')
            ->where('type', 'BRANCH');
    }

    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            UserHasBranchDealer::class,
            'branch_id',
            'id',
            'id',
            'user_id'
        )->where('user_has_branch_dealers.type', 'BRANCH');
    }

    public function toSyncPayload(): array
    {
        return [
            'code'     => $this->branch_code,
            'name'     => $this->name,
            'location' => $this->location,
            'status'   => $this->status,
            'remarks'  => $this->remarks,
        ];
    }
}
