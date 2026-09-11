<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Services\UserSettings\UserManagementService;
use App\Services\UserSettings\UserManagementSyncPreparationService;

class ProfileController extends Controller
{

    protected $userManagementService;
    protected $userManagementSyncPreparationService;

    public function __construct(UserManagementService $userManagementService, UserManagementSyncPreparationService $userManagementSyncPreparationService)
    {
        $this->userManagementService = $userManagementService;
        $this->userManagementSyncPreparationService = $userManagementSyncPreparationService;
    }

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = User::with([
            'roles',
            'branchDealerUser.branch',
            'branchDealerUser.dealer',
            'headOfficeUser.group',
            'headOfficeUser.division',
            'headOfficeUser.department',
            'headOfficeUser.section',
        ])->where('id', Auth::user()->id)->first();
        
        if ($user->isBranchDealer === 2) {
            $user->area = $this->userManagementSyncPreparationService->prepareHeadOfficeOrganizationData($user->headOfficeUser);
        } else {
            $user->area = $this->userManagementSyncPreparationService->prepareBranchDealerData($user);
        }

        $user->role = $user->roles->first()?->name;

        [ $user->branchId, $user->dealerId, $user->type ] = $this->userManagementService->prepareBranchDealerType($user);
            
        return Inertia::render('settings/profile', [
            'user' => $user,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    public function photoUpdate(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120']
            ]);

            $user = $request->user();
            $file = $request->file('profile_picture');
            $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists("profile_pictures/{$user->profile_picture}")) {
                Storage::disk('public')->delete("profile_pictures/{$user->profile_picture}");
            }

            // Store new file and update user
            $file->storeAs('profile_pictures', $filename, 'public');
            $user->update(['profile_picture' => $filename]);

            return to_route('profile.edit')->with(['success' => 'Profile Photo uploaded successfully.']);
        } catch (\Throwable $th) {
            Log::error("Profile picture upload failed: " . $th->getMessage());

            return redirect()->back()->withErrors([
                'general' => 'Failed to update profile photo: ' . $th->getMessage()
            ])->withInput();
        }
    }

    public function photoRemove(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->profile_picture && Storage::disk('public')->exists("profile_pictures/{$user->profile_picture}")) {
                Storage::disk('public')->delete("profile_pictures/{$user->profile_picture}");
            }

            $user->update(['profile_picture' => null]);

            return to_route('profile.edit')->with(['success' => 'Profile Photo removed successfully.']);
        } catch (\Throwable $th) {
            Log::error("Profile picture upload failed: " . $th->getMessage());

            return redirect()->back()->withErrors([
                'general' => 'Failed to remove profile photo: ' . $th->getMessage()
            ])->withInput();
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
