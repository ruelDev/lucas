<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Interactive SSO webhook tester.
 *
 * Usage:
 *   php artisan sso:test-webhook
 *   php artisan sso:test-webhook --type=user-sync
 *   php artisan sso:test-webhook --type=logout
 *   php artisan sso:test-webhook --url=http://127.0.0.1:8001
 *   php artisan sso:test-webhook --type=user-sync --bad-secret   (TC-044)
 */
class TestSsoWebhook extends Command
{
    protected $signature = 'sso:test-webhook
        {--type=       : Webhook type: user-sync or logout}
        {--url=        : Override the target base URL (defaults to APP_URL)}
        {--bad-secret  : Use a wrong HMAC secret to test TC-044 (401 rejection)}';

    protected $description = 'Interactively send a signed SSO webhook to LUCAS for local testing (TC-043 / TC-044)';

    public function handle(): int
    {
        $this->line('');
        $this->line('<options=bold>SSO Webhook Tester — TC-043 / TC-044</>');
        $this->line('─────────────────────────────────────────');

        // ── Pick webhook type ────────────────────────────────────────────────
        $type = $this->option('type');
        if (!$type) {
            $type = $this->choice('Which webhook do you want to test?', [
                'user-sync' => 'POST /sso/user-sync  — sync a user profile',
                'logout'    => 'POST /sso/logout-webhook — force-logout a user',
            ], 'user-sync');

            // choice() returns the label string when keys are set; normalise it
            $type = match (true) {
                str_contains($type, 'user-sync') => 'user-sync',
                str_contains($type, 'logout')    => 'logout',
                default                           => $type,
            };
        }

        if (!in_array($type, ['user-sync', 'logout'])) {
            $this->error("Unknown type [{$type}]. Use: user-sync or logout.");
            return self::FAILURE;
        }

        // ── Resolve employee_id (pick from DB or enter manually) ─────────────
        $employeeId = $this->resolveEmployeeId();
        if (!$employeeId) {
            return self::FAILURE;
        }

        // ── Build payload ────────────────────────────────────────────────────
        if ($type === 'user-sync') {
            $payload = $this->buildUserSyncPayload($employeeId);
        } else {
            $payload = $this->buildLogoutPayload($employeeId);
        }

        // ── Target URL ───────────────────────────────────────────────────────
        $base    = rtrim($this->option('url') ?: config('app.url'), '/');
        $path    = $type === 'user-sync' ? '/sso/user-sync' : '/sso/logout-webhook';
        $fullUrl = $base . $path;

        // ── Sign the request ─────────────────────────────────────────────────
        $secret    = $this->option('bad-secret')
            ? 'this-is-intentionally-wrong-secret'
            : config('sso.webhook_secret');
        $timestamp = (string) time();
        $rawBody   = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $rawBody, $secret);

        if ($this->option('bad-secret')) {
            $this->warn('⚠  Using WRONG secret — expecting 401 (TC-044)');
        }

        // ── Preview ──────────────────────────────────────────────────────────
        $this->line('');
        $this->line('<options=bold>Request Preview</>');
        $this->table(['Field', 'Value'], [
            ['URL',          "POST {$fullUrl}"],
            ['employee_id',  $employeeId],
            ['X-SSO-Timestamp', $timestamp],
            ['X-SSO-Signature', substr($signature, 0, 16) . '…'],
            ['Payload',      $rawBody],
        ]);

        if (!$this->confirm('Send this request?', true)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        // ── Send ─────────────────────────────────────────────────────────────
        $ch = curl_init($fullUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $rawBody,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-SSO-Signature: ' . $signature,
                'X-SSO-Timestamp: ' . $timestamp,
            ],
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS | CURLPROTO_HTTP,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        $this->line('');
        $this->line('<options=bold>Response</>');

        if ($curlError) {
            $this->error("cURL error: {$curlError}");
            return self::FAILURE;
        }

        $decoded = json_decode($responseBody, true);
        $pretty  = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $responseBody;

        $isBadSecret = $this->option('bad-secret');

        if (!$isBadSecret && $httpCode === 200) {
            $this->info("HTTP {$httpCode} OK");
            $this->line($pretty);
            $this->line('');
            $this->info('✓ TC-043 PASSED — correct HMAC accepted, payload processed');
            return self::SUCCESS;
        }

        if ($isBadSecret && $httpCode === 401) {
            $this->info("HTTP {$httpCode} Unauthorized");
            $this->line($pretty);
            $this->line('');
            $this->info('✓ TC-044 PASSED — wrong HMAC rejected with 401');
            return self::SUCCESS;
        }

        $this->error("HTTP {$httpCode}");
        $this->line($pretty);
        $this->line('');
        $this->warn($isBadSecret ? '✗ TC-044 FAILED — expected 401 but got ' . $httpCode : '✗ TC-043 FAILED');

        return self::FAILURE;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function resolveEmployeeId(): ?string
    {
        $choice = $this->choice(
            'How do you want to pick the employee?',
            ['Pick from existing users', 'Enter employee ID manually'],
            0
        );

        if (str_contains($choice, 'manually')) {
            return $this->ask('Enter employee_id');
        }

        // Load a sample of users from DB
        $users = User::select('employee_id', 'fname', 'lname', 'email')
            ->orderBy('fname')
            ->limit(20)
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users found in the database. Falling back to manual entry.');
            return $this->ask('Enter employee_id');
        }

        $options = $users->mapWithKeys(fn ($u) =>
            [$u->employee_id => "{$u->employee_id} — {$u->fname} {$u->lname} ({$u->email})"]
        )->toArray();

        $selected = $this->choice('Select a user', $options);

        // Extract the employee_id portion (before " — ")
        return explode(' — ', $selected)[0];
    }

    private function buildUserSyncPayload(string $employeeId): array
    {
        $this->line('');
        $this->line('<options=bold>User-Sync Payload</>');
        $this->line('Press <Enter> to accept the default shown in [brackets].');

        $user = User::where('employee_id', $employeeId)->first();

        $payload = [
            'employee_id'    => $employeeId,
            'fname'          => $this->ask('fname',          $user?->fname          ?? 'Test'),
            'mname'          => $this->ask('mname',          $user?->mname          ?? ''),
            'lname'          => $this->ask('lname',          $user?->lname          ?? 'User'),
            'email'          => $this->ask('email',          $user?->email          ?? 'test@bmi.com'),
            'company'        => $this->ask('company',        $user?->company        ?? 'BMI'),
            'position'       => $this->ask('position',       $user?->position       ?? 'Staff'),
            'status'         => $this->ask('status',         $user?->status         ?? 'active'),
            'isReset'        => (int) $this->ask('isReset (0 or 1)', (string) ($user?->isReset ?? 0)),
            'isBranchDealer' => (int) $this->ask('isBranchDealer (0 or 1)', (string) ($user?->isBranchDealer ?? 0)),
            'hashed_password'=> $user?->getAuthPassword() ?? null,
        ];

        // Strip null fields so the webhook mirrors what SSO actually sends
        return array_filter($payload, fn ($v) => !is_null($v));
    }

    private function buildLogoutPayload(string $employeeId): array
    {
        $reason = $this->ask('reason', 'logout');
        return ['employee_id' => $employeeId, 'reason' => $reason];
    }
}
