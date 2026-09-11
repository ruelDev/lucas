<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionVersionIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip the sv check for the SSO callback route. When a user re-enters
        // LUCAS via the SSO tile their old LUCAS session may have a stale sv
        // (e.g. the logout webhook already reset session_version to 0 in the DB
        // while the browser still holds a cookie with sv = N). The callback
        // itself will regenerate the session and set the correct sv; letting
        // EnsureSessionVersionIsValid run here would kick the user before the
        // callback code ever executes.
        if ($request->routeIs('sso.callback')) {
            return $next($request);
        }

        if (Auth::check()) {

            $user = Auth::user();
            $sv        = session('sv');
            $dbVersion = $user->session_version;

            // if (session('sv') !== $user->session_version) {
            //     Auth::logout();

            //     $request->session()->invalidate();
            //     $request->session()->regenerateToken();

            //     return redirect()->route('login');
            // }

            if ($sv !== $dbVersion) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->header('X-Inertia')) {
                    // Inertia XHR request: return 409 Conflict so Inertia does a
                    // full-page reload to /login rather than a partial swap.
                    return response('', 409, ['X-Inertia-Location' => url('/login?kicked=1')]);
                }

                return redirect('/login?kicked=1');
            }


            Redis::setex(
                "user:{$user->id}:last_seen",
                (config('session.lifetime') * 60) + 30,
                now()->timestamp
            );
        }

        return $next($request);
    }
}
