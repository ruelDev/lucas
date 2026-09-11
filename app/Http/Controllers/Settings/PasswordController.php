<?php

namespace App\Http\Controllers\Settings;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Mail\PasswordChangeNotificationMail;
use App\Models\PasswordArchive;
use App\Models\User;
use App\Rules\HasLowercase;
use App\Rules\HasNumber;
use App\Rules\HasSymbol;
use App\Rules\HasUppercase;
use App\Rules\NotInKeywords;
use App\Rules\NotPalindrome;
use App\Rules\NotPreviouslyUsedPassword;
use App\Rules\NotWithinSevenDays;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Jobs\PushUserSyncToSsoJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        $user = Auth::user();
        $returnRoute = 'settings/password';

        if ($user->isReset === 1) {
            Session::put('firstReset', true);
            return Inertia::render($returnRoute)->with('firstReset', true);
        } elseif ($user && $user->password_changed_at && $user->password_changed_at < Carbon::now()->subMonths(3)) {
            Session::put('expiredReset', true);
            return Inertia::render($returnRoute)->with('expiredReset', true);
        }

        return Inertia::render($returnRoute);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $firstReset = $user->isReset === 0;
        $expiredReset = $user->password_changed_at
            && $user->password_changed_at < now()->subMonths(3);
        $isMandatoryChange = $firstReset || $expiredReset;

        $validated = $request->validate($this->rules($firstReset, $expiredReset));

        DB::beginTransaction();

        try {
            if (!$firstReset) {
                $this->handlePasswordArchive($user);
            }

            $this->updatePassword($user, $validated['password'], $firstReset, $expiredReset);

            session([
                'firstReset' => false,
                'expiredReset' => false,
            ]);
            
            if ($isMandatoryChange) {
                $user['title'] = "Change Password Notification";
                $this->sendPasswordChangeNotificationEmail($user);
            }

            Log::info('User changed password', ['user_id' => $user->id]);

            DB::commit();

            PushUserSyncToSsoJob::syncOrQueue($user->fresh());

            return $isMandatoryChange
                ? redirect()->route('dashboard')
                : back()->with('success', 'Password changed successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error changing user password', [
                'user_id' => $user->id,
                'error' => $th->getMessage()
            ]);

            return back()->withErrors([
                'error' => 'Failed to change password. Please try again.'
            ]);
        }
    }

    private function rules(bool $firstReset, bool $expiredReset): array
    {
        $basePasswordRules = [
            'required',
            'confirmed',
            'min:8',
            'max:20',

            new HasLowercase,
            new HasUppercase,
            new HasNumber,
            new HasSymbol,

            // Password::min(8)->max(20)->mixedCase()->letters()->numbers()->symbols(),
            new NotPalindrome,
            new NotInKeywords,
        ];

        if ($expiredReset) {
            $basePasswordRules[] = new NotPreviouslyUsedPassword;
            $basePasswordRules[] = new NotWithinSevenDays;
        }

        $requireCurrentPassword = !($firstReset || $expiredReset);

        return [
            'current_password' => $requireCurrentPassword
                ? ['required', 'current_password']
                : [],
            'password' => $basePasswordRules,
        ];
    }

    public function sendPasswordChangeNotificationEmail($user)
    {
        $user['title'] = 'Successful Password Change';
        return Mail::to($user->email)->send(new PasswordChangeNotificationMail($user));
    }

    private function handlePasswordArchive($user): void
    {
        $lastIds = PasswordArchive::where('user_id', $user->id)
            ->latest()
            ->take(4)
            ->pluck('id');

        PasswordArchive::where('user_id', $user->id)
            ->whereNotIn('id', $lastIds)
            ->delete();

        PasswordArchive::create([
            'user_id' => $user->id,
            'last_effective_date' => now()->toDateString(),
            'password' => $user->password,
        ]);
    }

    private function updatePassword($user, string $password, bool $firstReset, bool $expiredReset): void
    {
        $data = [
            'password' => Hash::make($password),
            'password_changed_at' => now(),
        ];

        if ($firstReset || $expiredReset) {
            $data['isReset'] = 0;
        }

        $user->update($data);
    }
}
