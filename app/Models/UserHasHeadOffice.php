<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserHasHeadOffice extends Model
{
    use HasFactory;
    use SoftDeletes;

    
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'location',
        'group_id',
        'division_id',
        'department_id',
        'section_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
