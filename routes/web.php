<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return to_route('login');
// })->name('home');
Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified', 'check_is_reset_pass', 'check_password_validity', 'session_timeout'])->group(function () {
    // Dashboard------------------------------------------------------
    // Route::get('dashboard', function () {
    //     return Inertia::render('dashboard');
    // })->name('dashboard');
    // Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----------------------------------------------------------------

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('auditLogs')
        ->middleware("permission:audit_logs.view");

    Route::post('audit-logs/export', [AuditLogController::class, 'export'])
        ->name('audit-logs.export')
        ->middleware("permission:audit_logs.export");
});


require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/userSettings.php';
require_once __DIR__ . '/masterSetup.php';
require_once __DIR__ . '/reports.php';
