<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use RuntimeException;

/**
 * Verifies SSO-issued JWT handoff tokens locally using the SSO's RSA public key.
 *
 * No network call is made during verification — the public key is read once
 * from config/env and cached for the process lifetime. This means LUCAS can
 * authenticate SSO users without depending on SSO availability at request time.
 */
class SsoVerificationService
{
    private ?string $publicKey = null;

    public function __construct() {}

    /**
     * Verify an SSO handoff token.
     *
     * @param  string $token  The JWT received in ?sso_token=
     * @return array          Decoded, validated claims
     * @throws RuntimeException on any validation failure
     */
    public function verify(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Malformed SSO token.');
        }

        [$header, $payload, $signature] = $parts;

        // Decode header to confirm algorithm (defence against algorithm confusion)
        $headerData = json_decode($this->base64UrlDecode($header), true);
        if (($headerData['alg'] ?? '') !== 'RS256') {
            throw new RuntimeException('Unexpected token algorithm.');
        }

        // Verify RSA-SHA256 signature using SSO public key — pure local crypto, no HTTP
        $valid = openssl_verify(
            "{$header}.{$payload}",
            $this->base64UrlDecode($signature),
            $this->publicKey ??= $this->resolvePublicKey(),
            OPENSSL_ALGO_SHA256
        );

        if ($valid !== 1) {
            throw new RuntimeException('Invalid token signature.');
        }

        $claims = json_decode($this->base64UrlDecode($payload), true);

        if (!is_array($claims)) {
            throw new RuntimeException('Malformed token payload.');
        }

        // Validate standard claims
        $this->validateClaims($claims);

        // Single-use enforcement, entirely local (no round trip to SSO): the
        // jti functions as a nonce, exp as its freshness bound. First caller
        // to claim a given jti wins; a replayed URL within the 5-minute
        // window is rejected even if SSO itself hasn't been notified yet
        // (see ConsumeSsoTokenJob, which reports consumption to SSO async).
        $this->claimNonce($claims);

        return $claims;
    }

    /**
     * Atomically claim a token's jti so it cannot be verified a second time.
     * Uses SETNX (claim) + EXPIRE (bound the key's lifetime to the token's
     * own remaining validity) rather than a combined SET...NX EX option
     * array, since that option syntax differs between the phpredis and
     * predis clients — SETNX/EXPIRE are plain commands, identical on both.
     */
    private function claimNonce(array $claims): void
    {
        $jti = $claims['jti'] ?? null;

        if (!$jti) {
            throw new RuntimeException('SSO token missing jti.');
        }

        $key = "sso:jti:{$jti}";
        $ttl = max(1, (int) ($claims['exp'] ?? time()) - time());

        if (!Redis::setnx($key, 1)) {
            throw new RuntimeException('SSO token already used.');
        }

        Redis::expire($key, $ttl);
    }

    /**
     * Validate standard JWT claims (exp, iss, aud).
     */
    private function validateClaims(array $claims): void
    {
        if (($claims['exp'] ?? 0) < time()) {
            throw new RuntimeException('SSO token has expired.');
        }

        $expectedIssuer = rtrim(trim(config('sso.issuer')), '/');
        $actualIssuer   = rtrim(trim($claims['iss'] ?? ''), '/');

        if ($expectedIssuer !== $actualIssuer) {
            throw new RuntimeException('Invalid token issuer. Expected: [' . $expectedIssuer . '] Got: [' . $actualIssuer . ']');
        }

        $expectedAudience = config('sso.system_code');
        if (($claims['aud'] ?? '') !== $expectedAudience) {
            throw new RuntimeException('Token audience mismatch.');
        }
    }

    /**
     * Verify the HMAC-SHA256 signature on an incoming logout webhook.
     *
     * @param  string $rawBody    Raw JSON request body
     * @param  string $signature  Value of X-SSO-Signature header
     * @param  string $timestamp  Value of X-SSO-Timestamp header (unix seconds)
     * @throws RuntimeException on invalid signature or stale timestamp
     */
    public function verifyWebhookSignature(string $rawBody, string $signature, string $timestamp): void
    {
        // Replay protection: reject requests older than configured max age
        $maxAge = (int) config('sso.webhook_max_age', 60);
        if (abs(time() - (int) $timestamp) > $maxAge) {
            throw new RuntimeException('Webhook timestamp is too old (possible replay attack).');
        }

        $secret   = config('sso.webhook_secret');
        $expected = hash_hmac('sha256', $rawBody, $secret);

        if (!hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid webhook signature.');
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolvePublicKey(): string
    {
        // Prefer the PEM string from env (easiest for CI/cloud).
        // Validate it with OpenSSL before trusting it — a common issue is that
        // `.env` stores literal `\n` instead of real newlines, producing a
        // malformed PEM that openssl_verify cannot parse. If the env value is
        // present but invalid we fall through to the key file so the app
        // degrades gracefully rather than throwing a cryptic OpenSSL error.
        $pemFromEnv = config('sso.public_key');
        if ($pemFromEnv) {
            // Replace literal \n sequences (copy-paste artifact) with real newlines
            $pemFromEnv = str_replace('\n', "\n", $pemFromEnv);

            if (openssl_pkey_get_public($pemFromEnv) !== false) {
                return $pemFromEnv;
            }

            // Log a warning so the developer knows the env value is bad
            \Illuminate\Support\Facades\Log::channel('sso')->warning(
                'SSO_PUBLIC_KEY in env is not a valid RSA public key — falling back to key file. ' .
                'Run: php artisan sso:fetch-public-key'
            );
        }

        // Fall back to a key file bootstrapped by `php artisan sso:fetch-public-key`
        $keyPath = config('sso.public_key_path');
        if ($keyPath && file_exists($keyPath)) {
            $pemFromFile = file_get_contents($keyPath);

            if (openssl_pkey_get_public($pemFromFile) !== false) {
                return $pemFromFile;
            }

            \Illuminate\Support\Facades\Log::channel('sso')->warning(
                'SSO public key file is not a valid RSA public key. Run: php artisan sso:fetch-public-key --force'
            );
        }

        throw new RuntimeException(
            'SSO public key not configured or invalid. Run: php artisan sso:fetch-public-key'
        );
    }

    private function base64UrlDecode(string $data): string
    {
        $pad = (4 - strlen($data) % 4) % 4;
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', $pad));
    }
}
