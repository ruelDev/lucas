<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'remarks'
    ];

    public function headOfficeUser()
    {
        return $this->hasMany(UserHasHeadOffice::class);
    }

    public function division()
    {
        return $this->hasMany(Division::class);
    }

    public function department()
    {
        return $this->hasMany(Department::class);
    }

    public function section()
    {
        return $this->hasMany(Section::class);
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%");
    }

    public function toSyncPayload(): array
    {
        return [
            'code'        => $this->code,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'remarks'     => $this->remarks,
        ];
    }
}
