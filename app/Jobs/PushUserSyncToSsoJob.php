<?php

namespace App\Jobs;

use App\Models\User;
use App\Support\SsoCurlOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushUserSyncToSsoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry a few times in case SSO is temporarily down.
     */
    public int $tries = 5;
    public int $timeout = 15;
    public array $backoff = [30, 60, 120, 300]; // seconds between retries

    public function __construct(private readonly User $user) {}

    /**
     * Try to sync immediately (dispatchSync). If SSO is unreachable, fall back
     * to dispatch() so the job is queued and retried automatically once SSO
     * comes back online (requires a queue worker: php artisan queue:work).
     */
    public static function syncOrQueue(User $user): void
    {
        try {
            static::dispatchSync($user);
        } catch (\Throwable $e) {
            Log::warning('PushUserSyncToSsoJob: immediate sync failed, queuing for retry. Error: ' . $e->getMessage());
            static::dispatch($user);
        }
    }

    public function handle(): void
    {
        $ssoUrl       = rtrim(config('sso.url'), '/');
        $clientId     = config('sso.client_id');
        $clientSecret = config('sso.client_secret');

        if (!$ssoUrl || !$clientId || !$clientSecret) {
            Log::warning('PushUserSyncToSsoJob: SSO credentials not configured — skipping sync for [' . $this->user->employee_id . '].');
            return;
        }

        // Load branch/office relationships needed for SSO sync
        $user = $this->user->load([
            'branchDealerUser.branch',
            'branchDealerUser.dealer',
            'headOfficeUser.group',
            'headOfficeUser.division',
            'headOfficeUser.department',
            'headOfficeUser.section',
        ]);

        // Resolve branch identifier and location based on office type
        $branchDealerId = null;
        $location       = null;
        if ($user->isBranchDealer == 1) {
            // Branch or Dealer user — send the branch/dealer name as branchDealerId
            $rec            = $user->branchDealerUser;
            $branchDealerId = $rec?->type === 'BRANCH'
                ? $rec?->branch?->name
                : $rec?->dealer?->name;
        } elseif ($user->isBranchDealer == 2) {
            // Head Office user — send the location field
            $location = $user->headOfficeUser?->location;
        }

        $payload = json_encode([
            'employee_id'     => $user->employee_id,
            'fname'           => $user->fname,
            'mname'           => $user->mname,
            'lname'           => $user->lname,
            'email'           => $user->email,
            'company'         => $user->company,
            'position'        => $user->position,
            'status'          => $user->status,
            'isReset'         => $user->isReset,
            'isBranchDealer'  => $user->isBranchDealer,
            'expiration_date' => $user->expiration_date,
            'profile_picture' => $user->profile_picture,
            'remarks'         => $user->remarks,
            // Role is LUCAS-owned — synced to UserSystem.permissions in SSO only
            // (never overwrites SSO's own Spatie roles like SSO_ADMIN)
            'role'            => $user->getRoleNames()->first(),
            'branchDealerId'  => $branchDealerId,
            'location'        => $location,
            'organization'    => $this->resolveOrganizationName($user),
            // Send the bcrypt hash so SSO stays in sync when the user changes
            // their password locally (e.g. while SSO is offline / fallback login).
            'hashed_password' => $user->getAuthPassword(),
        ]);

        $ch = curl_init($ssoUrl . '/api/sso/user-sync');
        curl_setopt_array($ch, SsoCurlOptions::build([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
            ],
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException(sprintf(
                'PushUserSyncToSsoJob: SSO sync failed for [%s]. HTTP: %d, cURL: %s, Response: %s',
                $this->user->employee_id,
                $httpCode,
                $error ?: 'none',
                $response
            ));
        }
    }

    private function resolveOrganizationName(User $user): ?string
    {
        $ho = $user->headOfficeUser;

        // Determine leaf node and its hierarchy parents (most-specific wins).
        [$parents, $leaf] = match (true) {
            (bool) ($ho?->section_id    && $ho->section)    => [[$ho->group, $ho->division, $ho->department], $ho->section],
            (bool) ($ho?->department_id && $ho->department) => [[$ho->group, $ho->division], $ho->department],
            (bool) ($ho?->division_id   && $ho->division)   => [[$ho->group], $ho->division],
            (bool) ($ho?->group_id      && $ho->group)      => [[], $ho->group],
            default                                          => [null, null],
        };

        if (!$leaf) {
            return null;
        }

        $prefix = collect($parents)->filter()->pluck('code')->implode('->');
        return ($prefix ? $prefix . '->' : '') . $leaf->name;
    }

    /**
     * Called by Laravel after all retry attempts are exhausted.
     * The sync will be re-applied the next time the user's profile is changed,
     * or an admin can re-trigger it manually.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('PushUserSyncToSsoJob: all retries exhausted for [' . $this->user->employee_id . ']. SSO may be out of sync. Error: ' . $e->getMessage());
    }
}
