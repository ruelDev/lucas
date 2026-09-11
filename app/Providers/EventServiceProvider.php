<?php

namespace App\Providers;

use App\Listeners\LogSuccessfullLogin;
use App\Listeners\LogSuccessfullLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Listener services.
     */
    protected $listener = [
        Login::class => [LogSuccessfullLogin::class],
        Logout::class => [LogSuccessfullLogout::class]
    ];


    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        \Queue::before(function (JobProcessing $event) {
            Log::channel('queue_management')->info('Job Starting', [
                'job' => $event->job->resolveName(),
            ]);
        });

        \Queue::after(function (JobProcessed $event) {
            Log::channel('queue_management')->info('Job Completed', [
                'job' => $event->job->resolveName(),
            ]);
        });

        \Queue::failing(function (JobFailed $event) {
            Log::channel('queue_management')->info('Job Failed', [
                'job' => $event->job->resolveName(),
            ]);
        });
    }
}
