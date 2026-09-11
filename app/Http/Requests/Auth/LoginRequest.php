<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const AUTH_WARNING = 'warning';
    public const AUTH_LOCKED = 'locked';
    public const MULTI_SESSION = 'multi';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        Session::forget(['authWarning', 'authLocked', 'multiSession']);

        $user = User::where('employee_id', $this->employee_id)->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            return $this->handleFailedAttempt($user);
        }

        if ($user->status != 'active') {
            throw ValidationException::withMessages([
                'employee_id' => 'Your account is ' . $user->remarks . ' | Please Contact SAPS for Inquiry.',
            ]);
        }

        // if ($user->session_version > 0 && $hasActiveSession) {
        //     Session::put('multiSession', $user->id);
        //     return self::MULTI_SESSION;
        // }

        // Detect an existing active session. We check two conditions:
        //   1. session_version > 0 — someone logged in and didn't cleanly log out
        //   2. "user:{id}:last_seen" exists in Redis — the session is still alive
        //
        // EnsureSessionVersionIsValid middleware refreshes this Redis key on every
        // authenticated request with a TTL equal to the session lifetime (SESSION_LIFETIME).
        // When the user's session expires (timeout, server restart, Redis flush),
        // the key is automatically deleted — so hasActiveSession becomes false,
        // meaning it was a stale session_version from a timeout/tab-close, not a
        // real concurrent session. In that case we silently reset and allow login.
        // $hasActiveSession = Redis::exists("user:{$user->id}:last_seen");
        if ($user->session_version > 0) {
            $hasActiveSession = Redis::exists("user:{$user->id}:last_seen");

            if ($hasActiveSession) {
                // Real concurrent session on another device — store credentials so
                // killSession() can re-use them, then signal the controller to show
                // the multi-session confirmation dialog via flash data.
                Session::put('multiSession', [
                    'employee_id' => $this->employee_id,
                    'password'    => $this->password,
                ]);

                return self::MULTI_SESSION;
            }

            // Session timed out naturally — reset version and proceed with login
            $user->update(['session_version' => 0]);
        }


        RateLimiter::clear($this->throttleKey());

        $user->update(['failed_attempts' => 0]);

        Log::channel('session')->info('User Logged In', ['user_id' => $user->id]);

        Auth::attempt($this->only('employee_id', 'password'), $this->boolean('remember'));

        return null;
    }

    public function handleFailedAttempt($user)
    {
        if ($user && !Hash::check($this->password, $user->password)) {
            $user->increment('failed_attempts');

            if ($user->failed_attempts >= 3 && $user->failed_attempts < 5) {
                Session::put('authWarning', true);
                Session::flash('failedAttempts', $user->failed_attempts);
                Session::flash('remainingAttempts', 5 - $user->failed_attempts);
                return self::AUTH_WARNING;
            }

            if ($user->failed_attempts === 5) {
                Session::put('authLocked', true);
                $user->update([
                    'status' => 'inactive',
                    'remarks' => 'Locked'
                ]);
                return self::AUTH_LOCKED;
            }
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'employee_id' => __('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'employee_id' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('employee_id')) . '|' . $this->ip());
    }
}
