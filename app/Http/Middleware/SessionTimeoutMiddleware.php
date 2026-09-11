<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Redirect to SSO and invalidate the LUCAS session if the authenticated
     * user has been idle for longer than SESSION_LIFETIME minutes.
     *
     * The last-seen timestamp is written by EnsureSessionVersionIsValid on
     * every authenticated request using the key "user:{id}:last_seen".
     * This middleware must run BEFORE EnsureSessionVersionIsValid so that a
     * timed-out user is cleanly redirected without hitting the sv-mismatch path.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip on the SSO callback — the controller logs the user in fresh,
        // so a stale last_seen timestamp from a previous session must not
        // redirect them away before the handshake completes.
        if ($request->routeIs('sso.callback')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user        = Auth::user();
            $timeoutSecs = config('sso.idle_timeout') * 60;
            $lastSeen    = Redis::get("user:{$user->id}:last_seen");

            if ($lastSeen === null) {
                // Two cases when lastSeen is null:
                //   (a) Fresh login — EnsureSessionVersionIsValid hasn't written the key yet
                //       because it runs after this middleware. Detected by the one-shot
                //       'just_logged_in' flag set on every login and consumed here via pull()
                //       so it only ever skips the check once per login.
                //   (b) Redis TTL expired — user was idle beyond (idle_timeout + 2) min buffer.
                //       Flag is gone → treat as timed out.
                $isFreshLogin = (bool) session()->pull('just_logged_in', false);
                if (!$isFreshLogin) {
                    // Case (b): treat as timed out
                    $idleSeconds = $timeoutSecs + 1;
                } else {
                    // Case (a): first request after login, skip
                    return $next($request);
                }
            } else {
                $idleSeconds = now()->timestamp - (int) $lastSeen;
            }

            if ($idleSeconds > $timeoutSecs) {
                Log::channel('sso')->info('LUCAS session timed out due to inactivity', [
                    'user_id'      => $user->id,
                    'idle_seconds' => $idleSeconds,
                ]);

                Redis::del("user:{$user->id}:last_seen");

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $ssoUrl = rtrim(config('sso.url'), '/');

                if ($request->header('X-Inertia')) {
                    // Force a hard browser navigation for Inertia XHR requests
                    Log::channel('sso')->info('Session timeout: returning 409 Inertia XHR response', [
                        'user_id'            => $user->id,
                        'x_inertia_location' => $ssoUrl,
                    ]);
                    return response('', 409, ['X-Inertia-Location' => $ssoUrl]);
                }

                return redirect($ssoUrl);
            }
        }

        return $next($request);
    }
}
