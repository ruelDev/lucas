<?php

namespace App\Jobs;

use App\Support\SsoCurlOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Sends a Branch/Dealer/Group/Division/Department/Section change to SSO.
 *
 * Sync-or-queue, mirroring PushUserSyncToSsoJob/PushUserDeleteToSsoJob: a
 * synchronous push is tried first, and ANY failure — SSO unreachable, or SSO
 * rejecting the change (e.g. its own copy of this entity still has children/
 * users referencing it) — falls back to a queued retry instead of throwing
 * back to the caller. See OrgSyncClient::pushToSso() and docs/LUCAS with
 * SSO.md §5d for the tradeoff this accepts.
 */
class PushOrgSyncToSsoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 15;
    public array $backoff = [30, 60, 120, 300];

    public function __construct(
        private readonly string $type,
        private readonly string $action,
        private readonly array $payload,
        private readonly ?string $employee_id = null
    ) {}

    public static function syncOrQueue(string $type, string $action, array $payload, ?string $employee_id = null): void
    {
        try {
            static::dispatchSync($type, $action, $payload, $employee_id);
        } catch (\Throwable $e) {
            Log::channel('sso')->warning("PushOrgSyncToSsoJob: immediate sync failed for [{$type}:{$action}], queuing for retry. Error: " . $e->getMessage());
            // Was missing $employee_id here entirely, which would have thrown the same
            // ArgumentCountError this queued fallback exists to recover from — the retry
            // never actually got queued.
            static::dispatch($type, $action, $payload, $employee_id);
        }
    }

    public function handle(): void
    {
        $ssoUrl       = rtrim(config('sso.url'), '/');
        $clientId     = config('sso.client_id');
        $clientSecret = config('sso.client_secret');

        if (!$ssoUrl || !$clientId || !$clientSecret) {
            Log::channel('sso')->warning("PushOrgSyncToSsoJob: SSO credentials not configured — skipping sync for [{$this->type}:{$this->action}].");
            return;
        }

        $body = json_encode(array_merge($this->payload, [
            'employee_id' => $this->employee_id,
            'type'      => $this->type,
            'action'    => $this->action,
            'timestamp' => now()->timestamp,
        ]));

        $ch = curl_init($ssoUrl . '/api/sso/org-sync');
        curl_setopt_array($ch, SsoCurlOptions::build([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
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
                'PushOrgSyncToSsoJob: SSO sync failed for [%s:%s]. HTTP: %d, cURL: %s, Response: %s',
                $this->type,
                $this->action,
                $httpCode,
                $error ?: 'none',
                $response
            ));
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::channel('sso')->error("PushOrgSyncToSsoJob: all retries exhausted for [{$this->type}:{$this->action}]. SSO may be out of sync. Error: " . $e->getMessage());
    }
}
