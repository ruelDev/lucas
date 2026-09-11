<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AppVersion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\Response as HttpResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page, or redirect to SSO if it is reachable.
     */
    public function create(Request $request): InertiaResponse|RedirectResponse|HttpResponse
    {
        $ssoUrl     = config('sso.url');
        $systemCode = config('sso.system_code');
        $wasKicked  = $request->query('kicked') === '1';

        // SSO is unreachable OR the user was just kicked by a new session on another device.
        // In the kicked case we must NOT auto-redirect to SSO — doing so causes a silent
        // re-login which immediately kicks the other device and creates a ping-pong loop.
        if ($ssoUrl && $this->isSsoReachable($ssoUrl) && !$wasKicked) {
            // Inertia::location() forces a hard window.location navigation instead of
            // an Inertia AJAX follow — required for cross-origin redirects (port 8000).
            return Inertia::location(
                rtrim($ssoUrl, '/') . '/login?' . http_build_query(['return_to' => $systemCode])
            );
        }

        $appVersion = [];
        try {
            $appVersion = AppVersion::with(['releaseNotes' => function ($query) {
                $query->orderBy('sort_order', 'asc')->get();
            }])->orderBy('released_at', 'desc')->limit(5)->get();
        } catch (\Throwable) {
            // DB unavailable — login page renders without version history.
        }

        return Inertia::render('auth/login', [
            'appVersion' => $appVersion,
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'ssoUnavailable'   => !$wasKicked,
            'kicked'           => $wasKicked,
            'ssoLoginUrl'      => $wasKicked && $ssoUrl
                ? rtrim($ssoUrl, '/') . '/login?' . http_build_query(['return_to' => $systemCode])
                : null,
        ]);
    }

    /**
     * Debug endpoint — shows SSO reachability status.
     * Visit /sso-debug to verify SSO health check configuration.
     */
    public function ssoDebug(): \Illuminate\Http\JsonResponse
    {
        $ssoUrl    = config('sso.url');
        $reachable = $ssoUrl ? $this->isSsoReachable($ssoUrl) : false;

        $ch = curl_init(rtrim($ssoUrl, '/') . '/api/sso/public-key');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_NOBODY         => true,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);
        curl_exec($ch);
        $http    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        return response()->json([
            'sso_url'          => $ssoUrl,
            'sso_issuer'       => config('sso.issuer'),
            'system_code'      => config('sso.system_code'),
            'app_url'          => config('app.url'),
            'health_check_url' => rtrim($ssoUrl, '/') . '/api/sso/public-key',
            'http_status'      => $http,
            'curl_error'       => $curlErr ?: null,
            'sso_reachable'    => $reachable,
            'will_redirect'    => $reachable ? 'YES → to SSO login' : 'NO → shows local form',
        ]);
    }

    /**
     * Ping the SSO public-key endpoint to check if it is up.
     * Short timeout so users are not kept waiting when SSO is down.
     */
    private function isSsoReachable(string $ssoUrl): bool
    {
        try {
            $ch = curl_init(rtrim($ssoUrl, '/') . '/sso-health-check');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            ]);
            $body = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http !== 200 || $body === false) {
                return false;
            }

            // Only consider SSO reachable when its database is also healthy.
            $payload = json_decode($body, true);
            return isset($payload['sso_reachable']) && $payload['sso_reachable'] === true;
        } catch (\Throwable) {
            return false;
        }
    }


    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $result = $request->authenticate();

        $responseMap = [
            LoginRequest::MULTI_SESSION => 'showMultiSessionModal',
            LoginRequest::AUTH_WARNING => 'showAccountWarningModal',
            LoginRequest::AUTH_LOCKED => 'showAccountLockedModal',
        ];

        if (isset($responseMap[$result])) {
            // Use microtime() as the flash value instead of a static `true` so that
            // React's useEffect (which watches this prop as a dependency) always detects
            // a value change and re-fires, even when the user cancels the dialog and
            // re-submits the form — without this the dialog stays closed on re-submit.
            return back()->with($responseMap[$result], microtime(true));
        }

        // No issues - proceed with normal login flow
        $request->session()->regenerate();

        $userId = Auth::id();
        $user = User::find($userId);

        $this->clearUserRedisCache($userId);

        // not good in sso since we need to create a sv mismatch to kick other sessions,
        // this condition only works for the first concurent login. after that any numnberof additional sessions can coexist without ever kicking each other.
        // if ($user && $user->session_version === 0) {
        //     $user->increment('session_version');
        // }

        // Bump session_version so any other device with a stale sv is kicked on
        // their next request via EnsureSessionVersionIsValid.
        $user->increment('session_version');
        session([
            'sv'             => $user->session_version,
            // One-shot flag for SessionTimeoutMiddleware: skips the idle check on the
            // very first post-login request (when the Redis key doesn't exist yet).
            // Consumed via pull() so it cannot mask a genuine idle timeout later.
            'just_logged_in' => true,
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Deletes existing cache of the user for logout
     */
    public function clearUserRedisCache($userId)
    {
        Redis::del("user:{$userId}:last_seen");
    }

    /**
     * Destroy an authenticated session.
     *
     * If the user authenticated via SSO and SSO is still reachable, redirect
     * to the SSO GET logout endpoint so the SSO session is also terminated and
     * logout webhooks are propagated to all connected systems.
     * Fall back to local redirect when SSO is unreachable.
     */
    public function destroy(Request $request): RedirectResponse|HttpResponse
    {
        $wasSsoAuth = session('sso_authenticated', false);
        $userId = Auth::id();
        $user = User::find($userId);

        Log::channel('session')->info('User Logged Out', ['user_id' => $userId]);

        $this->clearUserRedisCache($userId);

        Auth::logout();

        if ($user) {
            $user->update(['session_version' => 0]);
        }


        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If the user came in via SSO, send them to the SSO logout endpoint so
        // the SSO session is destroyed and other systems receive the webhook.
        // Inertia::location() forces a hard window.location navigation instead of
        // an Inertia AJAX follow — required for cross-origin redirects (port 8000).
        if ($wasSsoAuth) {
            $ssoUrl = config('sso.url');
            if ($ssoUrl && $this->isSsoReachable($ssoUrl)) {
                return Inertia::location(rtrim($ssoUrl, '/') . '/sso-logout');
            }
        }

        return redirect('/');
    }

    public function killSession(Request $request)
    {
        $credentials = Session::get('multiSession');

        if (!$credentials) {
            return redirect()->route('login');
        }

        $user = User::where('employee_id', $credentials['employee_id'])->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            Session::forget('multiSession');
            return redirect()->route('login');
        }

        // Evict all other sessions by incrementing directly.
        // Do NOT reset to 0 first — that produces sv=1 which Browser A already
        // holds, so Browser A would not be kicked. Incrementing: DB was N → N+1,
        // Browser A has sv=N ≠ N+1 → kicked.
        $user->increment('session_version');

        $user->update(['failed_attempts' => 0]);

        Auth::login($user);
        $request->session()->regenerate();

        session([
            'sv'             => $user->session_version,
            'just_logged_in' => true,
        ]);
        Session::forget('multiSession');

        Log::channel('session')->info('User killed other sessions and logged in', ['user_id' => $user->id]);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
