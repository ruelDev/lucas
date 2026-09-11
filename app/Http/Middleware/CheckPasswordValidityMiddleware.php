<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordValidityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if($user && $user->password_changed_at && $user->password_changed_at < Carbon::now()->subMonths(3))
        {
            Session::put('expiredReset', true);
            return to_route('password.edit')->with('expiredReset', true);
        }

        return $next($request);
    }
}
