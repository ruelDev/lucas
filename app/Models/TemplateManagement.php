<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateManagement extends Model
{
      use SoftDeletes;
      protected $fillable =[
        'title',
        'content',
        'status'
    ];
}
