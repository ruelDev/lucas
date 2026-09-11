<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{

    protected $fillable = [
        'user_id',
        'event',
        'model',
        'module',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $cast = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeSearch($query, $value)
    {
        if (empty($value)) {
            return;
        }

        $terms = preg_split('/\s+/', trim($value));

        $query->where(function ($q) use ($value, $terms) {
            $q->where('model', 'like', "%{$value}%")
                ->orWhere('module', 'like', "%{$value}%")
                ->orWhere('created_at', 'like', "%{$value}%")
                ->orWhere('event', 'like', "%{$value}%")
                ->orWhereHas('user', function ($u) use ($terms) {
                    $u->where(function ($sub) use ($terms) {
                        foreach ($terms as $term) {
                            $sub->whereRaw("CONCAT_WS(' ', fname, mname, lname) LIKE ?", ['%' . $term . '%']);
                        }
                    });
                });
        });
    }
}
