<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'dealer_code',
        'location',
        'bank_account_name',
        'bank_account',
        'status',
        'remarks',
    ];

    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserHasBranchDealer::class, 'branch_id')
            ->where('type', 'DEALER');
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
        )->where('user_has_branch_dealers.type', 'DEALER');
    }

    public function toSyncPayload(): array
    {
        return [
            'code'              => $this->dealer_code,
            'name'              => $this->name,
            'location'          => $this->location,
            'bank_account_name' => $this->bank_account_name,
            'bank_account'      => $this->bank_account,
            'status'            => $this->status,
            'remarks'           => $this->remarks,
        ];
    }
}
