<?php

namespace App\Jobs;

use App\Support\SsoCurlOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushUserDeleteToSsoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 15;
    public array $backoff = [30, 60, 120, 300];

    public function __construct(private readonly string $employeeId) {}

    public static function deleteOrQueue(string $employeeId): void
    {
        try {
            static::dispatchSync($employeeId);
        } catch (\Throwable $e) {
            Log::warning('PushUserDeleteToSsoJob: immediate delete failed, queuing for retry. Error: ' . $e->getMessage());
            static::dispatch($employeeId);
        }
    }

    public function handle(): void
    {
        $ssoUrl       = rtrim(config('sso.url'), '/');
        $clientId     = config('sso.client_id');
        $clientSecret = config('sso.client_secret');

        if (!$ssoUrl || !$clientId || !$clientSecret) {
            Log::warning('PushUserDeleteToSsoJob: SSO credentials not configured — skipping delete for [' . $this->employeeId . '].');
            return;
        }

        $payload = json_encode(['employee_id' => $this->employeeId]);

        $ch = curl_init($ssoUrl . '/api/sso/user-delete');
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
                'PushUserDeleteToSsoJob: SSO delete failed for [%s]. HTTP: %d, cURL: %s, Response: %s',
                $this->employeeId,
                $httpCode,
                $error ?: 'none',
                $response
            ));
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('PushUserDeleteToSsoJob: all retries exhausted for [' . $this->employeeId . ']. SSO may be out of sync. Error: ' . $e->getMessage());
    }
}
