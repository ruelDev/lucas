<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDetailsFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONELEA.LEA_ASSET_DTL';
    protected $primaryKey = 'assetid';

    
}
