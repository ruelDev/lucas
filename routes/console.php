<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountExpiryMail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ------------------FOR SCHEDULED COMMANDS AND JOBS------------------
$schedules = [
    'user_management' => '9:00',
    'offline_search_facility' => '9:30',
];

// ------------------FOR USER MANAGEMENT------------------
// for updating user account status into expired
Schedule::command('users:check-expiration')->dailyAt($schedules['user_management']);
// to send email message 5 days before account expiration
Schedule::command('users:warning-expiration')->dailyAt($schedules['user_management']);
// to send email message 5 days before password expiration
Schedule::command('users:warning-password-expiration')->dailyAt($schedules['user_management']);

// ------------------FOR OSF------------------
// Schedule::command('osf:client-record')->dailyAt('09:30');
// Schedule::command('osf:client-account')->dailyAt('10:00');
// Schedule::command('osf:lms-record')->dailyAt('10:30');
// Schedule::command('osf:los-record')->dailyAt('11:00');

