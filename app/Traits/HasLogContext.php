<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HasLogContext
{
    public function getLogContext(Request $request, array $extra = []): array
    {
        $user = Auth::user();

        return array_merge([
            'user_id'    => $user?->id,
            'ip_address' => $request->ip(),
        ], $extra);
    }
}
