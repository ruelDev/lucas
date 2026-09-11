<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'code',
        'description',
        'group_id',
        'division_id',
        'status',
        'remarks'
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function headOfficeUser()
    {
        return $this->hasMany(UserHasHeadOffice::class);
    }

    public function section()
    {
        return $this->hasMany(Section::class);
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhereHas('division', function ($d) use ($value) {
                $d->where('name', 'like', "%{$value}%");
            })
            ->orWhereHas('division.group', function ($g) use ($value) {
                $g->where('name', 'like', "%{$value}%");
            });
    }

    public function toSyncPayload(): array
    {
        return [
            'code'          => $this->code,
            'name'          => $this->name,
            'description'   => $this->description,
            'group_code'    => $this->group?->code,
            'division_code' => $this->division?->code,
            'status'        => $this->status,
            'remarks'       => $this->remarks,
        ];
    }
}
