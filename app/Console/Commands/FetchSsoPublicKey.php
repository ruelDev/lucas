<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Fetch and cache the SSO RSA public key locally.
 *
 * Run this once during deployment or after the SSO public key changes.
 * After this, LUCAS verifies JWTs using the local key — no SSO network call
 * is needed at request time.
 *
 * Usage:
 *   php artisan sso:fetch-public-key
 *   php artisan sso:fetch-public-key --force   (overwrite existing key)
 */
class FetchSsoPublicKey extends Command
{
    protected $signature   = 'sso:fetch-public-key {--force : Overwrite existing key file}';
    protected $description = 'Fetch and store the SSO RSA public key for local JWT verification';

    public function handle(): int
    {
        $keyPath = config('sso.public_key_path');
        $ssoUrl  = rtrim(config('sso.url'), '/');

        $result = self::SUCCESS;

        // Validation: Check if SSO_URL is configured
        if (!$ssoUrl) {
            $this->error('SSO_URL is not set in your .env file.');
            $result = self::FAILURE;
        } elseif (file_exists($keyPath) && !$this->option('force')) {
            $this->info("Public key already exists at [{$keyPath}].");
            $this->line('Use --force to overwrite it.');
        } else {
            // Fetch and validate key
            $publicKey = $this->fetchAndValidatePublicKey($ssoUrl);
            if ($publicKey === null) {
                $result = self::FAILURE;
            } else {
                $this->savePublicKey($keyPath, $publicKey);
            }
        }

        return $result;
    }

    /**
     * Fetch the public key from SSO server.
     */
    private function fetchAndValidatePublicKey(string $ssoUrl): ?string
    {
        $this->info("Fetching SSO public key from [{$ssoUrl}/api/sso/public-key] ...");

        $ch = curl_init("{$ssoUrl}/api/sso/public-key");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS | CURLPROTO_HTTP,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        $pem = null;

        if ($error) {
            $this->error("cURL error: {$error}");
        } elseif ($httpCode !== 200) {
            $this->error("SSO server returned HTTP {$httpCode}. Check SSO_URL in .env.");
        } else {
            $data = json_decode($response, true);
            $pem  = $data['public_key'] ?? null;

            if (!$pem || !str_contains($pem, 'PUBLIC KEY')) {
                $this->error('Response did not contain a valid PEM public key.');
                $pem = null;
            } elseif (!openssl_pkey_get_public($pem)) {
                $this->error('The key returned by SSO is not a valid RSA public key.');
                $pem = null;
            }
        }

        return $pem;
    }

    /**
     * Save the public key to storage.
     */
    private function savePublicKey(string $keyPath, string $pem): void
    {
        $dir = dirname($keyPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($keyPath, $pem);
        chmod($keyPath, 0640);

        $this->info("SSO public key saved to [{$keyPath}].");
        $this->line('');
        $this->line('Add this to your .env for environments where file access is restricted:');
        $this->line('SSO_PUBLIC_KEY="' . str_replace(["\n", "\r"], '\n', trim($pem)) . '"');
    }
}
