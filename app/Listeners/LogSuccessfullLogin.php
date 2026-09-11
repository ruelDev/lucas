<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\SessionLogs;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogSuccessfullLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->id,
            'event' => 'LOGGED IN',
            'model' => null,
            'module' => 'Session',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        SessionLogs::create([
            'user_id' => $event->user->id,
            'session_type' => 'LOGIN',
        ]);
    }
}
