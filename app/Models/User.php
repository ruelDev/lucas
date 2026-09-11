<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'employee_id',
        'email',
        'company',
        'position',
        'password',
        'isReset',
        'isBranchDealer',
        'isAlternateUser',
        'status',
        'remarks',
        'session_version',
        'expiration_date',
        'profile_picture',
        'password_changed_at',
        'failed_attempts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'session_version'   => 'integer',
            'isReset'           => 'integer',
        ];
    }

    public function scopeSearch($query, $value)
    {
        $terms = preg_split('/\s+/', trim($value));

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->whereRaw("CONCAT_WS(' ', fname, mname, lname) LIKE ?", ['%' . $term . '%']);
            }
        })
            ->orWhere('employee_id', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('company', 'like', "%{$value}%")

            ->orWhereHas('roles', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })

            ->orWhereHas('branchDealerUser.branch', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })

            ->orWhereHas('branchDealerUser.dealer', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })

            ->orWhereHas('headOfficeUser.group', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })
            ->orWhereHas('headOfficeUser.division', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })
            ->orWhereHas('headOfficeUser.department', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })
            ->orWhereHas('headOfficeUser.section', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            });
    }

    public function branchDealerUser()
    {
        return $this->hasOne(UserHasBranchDealer::class);
    }

    public function getBranchAttribute()
    {
        return $this->branchDealerUser?->type === 'BRANCH'
            ? $this->branchDealerUser->branch
            : null;
    }

    public function getDealerAttribute()
    {
        return $this->branchDealerUser?->type === 'DEALER'
            ? $this->branchDealerUser->dealer
            : null;
    }

    public function getBranchOrDealerAttribute()
    {
        return $this->branchDealerUser?->assignable;
    }

    public function headOfficeUser()
    {
        return $this->hasOne(UserHasHeadOffice::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function sessionLogs()
    {
        return $this->hasMany(SessionLogs::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->deleted_at);
    }
}
