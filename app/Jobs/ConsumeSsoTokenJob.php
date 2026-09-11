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
 * Best-effort notification to SSO that a handoff token has been used, so SSO
 * can mark it consumed/revoked (SsoController::consumeToken()) and reject any
 * further replay of the same jti. Never blocks or fails the login itself —
 * the login already succeeded locally by the time this fires; single-use
 * enforcement against replay is handled independently and synchronously by
 * SsoVerificationService's local jti claim (Redis), not by this job landing.
 */
class ConsumeSsoTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 10;
    public array $backoff = [15, 60];

    public function __construct(private readonly string $jti) {}

    public static function notifyOrQueue(string $jti): void
    {
        try {
            static::dispatchSync($jti);
        } catch (\Throwable $e) {
            Log::channel('sso')->warning('ConsumeSsoTokenJob: immediate notify failed, queuing for retry. Error: ' . $e->getMessage());
            static::dispatch($jti);
        }
    }

    public function handle(): void
    {
        $ssoUrl       = rtrim(config('sso.url'), '/');
        $clientId     = config('sso.client_id');
        $clientSecret = config('sso.client_secret');

        if (!$ssoUrl || !$clientId || !$clientSecret) {
            Log::channel('sso')->warning('ConsumeSsoTokenJob: SSO credentials not configured — skipping consume for [' . $this->jti . '].');
            return;
        }

        $payload = json_encode(['jti' => $this->jti]);

        $ch = curl_init($ssoUrl . '/api/sso/consume-token');
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
                'ConsumeSsoTokenJob: consume-token failed for [%s]. HTTP: %d, cURL: %s, Response: %s',
                $this->jti,
                $httpCode,
                $error ?: 'none',
                $response
            ));
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::channel('sso')->error('ConsumeSsoTokenJob: all retries exhausted for [' . $this->jti . ']. Error: ' . $e->getMessage());
    }
}
