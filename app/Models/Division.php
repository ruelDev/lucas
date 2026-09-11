<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'code',
        'description',
        'group_id',
        'status',
        'remarks'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function headOfficeUser()
    {
        return $this->hasMany(UserHasHeadOffice::class);
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
        $query->where('name', 'like', "%{$value}%")
            ->orWhereHas('group', function ($g) use ($value) {
                $g->where('name', 'like', "%{$value}%");
            });
    }

    public function toSyncPayload(): array
    {
        return [
            'code'        => $this->code,
            'name'        => $this->name,
            'description' => $this->description,
            'group_code'  => $this->group?->code,
            'status'      => $this->status,
            'remarks'     => $this->remarks,
        ];
    }
}
