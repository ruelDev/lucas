<?php

use App\Http\Middleware\CheckIsResetPasswordMiddleware;
use App\Http\Middleware\CheckPasswordValidityMiddleware;
use App\Http\Middleware\EnsureSessionVersionIsValid;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SessionTimeoutMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Exclude SSO webhooks from CSRF — verified by HMAC-SHA256 instead.
        // Exclude logout — if the session has already expired the CSRF token is gone
        // anyway, and a CSRF attack on logout only logs someone out (no data exposure).
        $middleware->validateCsrfTokens(except: [
            'sso/logout-webhook',
            'sso/user-sync',
            'sso/user-delete',
            'sso/check-user-transactions',
            'sso/audit-log',
            'sso/org-sync-webhook',
            'logout',
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check_is_reset_pass' => CheckIsResetPasswordMiddleware::class,
            'check_password_validity' => CheckPasswordValidityMiddleware::class,
            'session_timeout' => SessionTimeoutMiddleware::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SessionTimeoutMiddleware::class,
            EnsureSessionVersionIsValid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $statusCode = $response->getStatusCode();

            if (! in_array($statusCode, [403, 404, 419, 429, 500, 503])) {
                return $response;
            }

            // 419 means the PHP session expired and the CSRF token is stale.
            // Showing a dead error page leaves the user stuck — instead, send
            // them back to SSO (or the login page) so they can re-authenticate.
            if ($statusCode === 419) {
                $ssoUrl = rtrim(config('sso.url', ''), '/');
                $target = $ssoUrl ?: url('/login');

                if ($request->header('X-Inertia')) {
                    return response('', 409, ['X-Inertia-Location' => $target]);
                }

                return redirect($target);
            }

            $statusMessages = [
                403 => "You don't have permission to access this page.",
                404 => "The page you're looking for doesn't exist.",
                429 => 'Too many requests. Please slow down.',
                500 => 'An unexpected server error occured, please contact System Administration Department.',
                503 => 'The service is temporarily unavailable. Please try again later.'
            ];

            $payload = [
                'status' => $statusCode,
                'message' => $statusMessages[$statusCode] ?? 'Something went wrong.'
            ];

            if (config('app.debug')) {
                $payload['debug'] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())
                        ->take(15)
                        ->map(fn($frame) => [
                            'file' => $frame['frame'] ?? '[internal]',
                            'line' => $frame['line'] ?? null,
                            'function' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? ''),
                            'args' => collect($frame['args'] ?? [])->map(fn($a) => gettype($a))->join(', '),
                        ])
                        ->toArray(),
                ];
            }

            return Inertia::render('error', $payload)
                ->toResponse($request)
                ->setStatusCode($statusCode);
        });
    })
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->create();
