<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SsoCallbackController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserSettings\HeadOfficeController;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------------------------
// SSO Routes (no auth required — these handle the handshake)
// -------------------------------------------------------------------------

// Callback from SSO server — receives ?sso_token=<jwt>
// NOTE: no 'guest' middleware — the callback must run even when an old session
// exists so it can replace it with the fresh SSO-authenticated session.
Route::get('sso/callback', [SsoCallbackController::class, 'callback'])
    ->name('sso.callback');

// Logout webhook from SSO server — HMAC-signed POST
// CSRF is excluded via bootstrap/app.php validateCsrfTokens(except: [...])
Route::post('sso/logout-webhook', [SsoCallbackController::class, 'logoutWebhook'])
    ->name('sso.logout-webhook');

// User profile sync webhook from SSO server — HMAC-signed POST
// CSRF is excluded via bootstrap/app.php validateCsrfTokens(except: [...])
Route::post('sso/user-sync', [SsoCallbackController::class, 'userSyncWebhook'])
    ->name('sso.user-sync');

// User delete webhook from SSO server — HMAC-signed POST
// CSRF is excluded via bootstrap/app.php validateCsrfTokens(except: [...])
Route::post('sso/user-delete', [SsoCallbackController::class, 'userDeleteWebhook'])
    ->name('sso.user-delete');

// Transaction check — SSO calls this before initiating a delete to confirm the
// user has no business transactions in Lucas. HMAC-signed POST.
// CSRF is excluded via bootstrap/app.php validateCsrfTokens(except: [...])
Route::post('sso/check-user-transactions', [SsoCallbackController::class, 'checkUserTransactionsWebhook'])
    ->name('sso.check-user-transactions');

// Audit log webhook from SSO server — HMAC-signed POST
// CSRF is excluded via bootstrap/app.php validateCsrfTokens(except: [...])
Route::post('sso/audit-log', [SsoCallbackController::class, 'auditLogWebhook'])
    ->name('sso.audit-log');

// CSRF is excluded via bootstrap\app.php validateCsrfTokens(except: [...])
Route::post('sso/org-sync-webhook', [SsoCallbackController::class, 'orgSyncWebhook'])
    ->name('sso.org-sync-webhook');

// Reference-data endpoints — no auth required; consumed by the SSO admin
// proxy so admins can pick branch/dealer from a dropdown.
Route::get('get-branch-options', [HeadOfficeController::class, 'getBranchOptions'])
    ->name('getBranchOptions.public');
Route::get('get-dealer-options', [HeadOfficeController::class, 'getDealerOptions'])
    ->name('getDealerOptions.public');

// ------------------------------------------------------------------------
// Standard Auth Routes
// ------------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::post('kill-session', [AuthenticatedSessionController::class, 'killSession'])
        ->name('kill-session');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::post('send-password-reset-mail', [PasswordResetLinkController::class, 'sendPasswordResetMail'])
        ->name('send.password.reset.mail');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
