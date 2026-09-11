<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasInfoLogChannel
{
    public function getInfoLogChannel($channel = '')
    {
        return Log::channel($channel);
    }
}
