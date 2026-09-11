<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\SessionLogs;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Request;

class LogSuccessfullLogout
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
    public function handle(Logout $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->id,
            'event' => 'LOGGED OUT',
            'model' => null,
            'module' => 'Session',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        SessionLogs::create([
            'user_id' => $event->user->id,
            'session_type' => 'LOGOUT',
        ]);
    }
}
