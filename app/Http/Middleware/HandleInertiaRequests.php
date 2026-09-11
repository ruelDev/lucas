<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'permissions' => fn () => $request->user()?->getAllPermissions()->pluck("name") ?? [],
                'roles' => fn () => $request->user()?->getRoleNames() ?? [],
            ],
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'sso' => [
                'url'              => config('sso.url'),
                'idle_timeout_ms'  => (int) config('sso.idle_timeout') * 60 * 1000,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'pdf_file' => $request->session()->get('pdf_file'),
            ],
            'alertDialog' => [
                'showAccountWarningModal' => fn () => $request->session()->get('showAccountWarningModal'),
                'showAccountLockedModal' => fn () => $request->session()->get('showAccountLockedModal'),
                'showMultiSessionModal' => fn () => $request->session()->get('showMultiSessionModal'),
                'showVerifyEidModal' => fn () => $request->session()->get('showVerifyEidModal'),
                'failedAttempts' => fn () => $request->session()->get('failedAttempts'),
                'remainingAttempts' => fn () => $request->session()->get('remainingAttempts'),
            ],
            'resetPassword' => [
                'firstReset' => fn () => $request->session()->get('firstReset'),
                'expiredReset' => fn () => $request->session()->get('expiredReset'),
            ]
        ];
    }
}
