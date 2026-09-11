<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'bank_branch_id',
        'bank_account',
        'account_type',
        'branch_micr_code',
        'branch_ifcs_code',
        'gl_code',
        'client_code',
        'rec_status',
        'maker_id',
        'maker_date',
        'author_id',
        'author_date',
        'bank_account_name',
        'drawing_power',
    ];
}
