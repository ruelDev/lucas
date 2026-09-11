<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssetsDetailsMFinnone extends Model
{
    protected $connection = 'oracle_lms';
    protected $table = 'FINNONELEA.LEA_ASSET_M';
    protected $primaryKey = 'agreementid';

    public function assetdetails(): BelongsTo
    {
        return $this->belongsTo(AssetDetailsFinnone::class, 'assetid', 'assetid');
    }

}
