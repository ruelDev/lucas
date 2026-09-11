<?php

namespace App\Console\Commands;

use App\Exceptions\SsoInvalidJsonResponseException;
use App\Exceptions\SsoServerUnreachableException;
use App\Exceptions\SsoUnexpectedHttpStatusException;
use Illuminate\Console\Command;

/**
 * Check SSO server health and LUCAS-side SSO configuration.
 *
 * Hits the SSO /sso-health-check endpoint and shows a formatted summary.
 * Also shows the LUCAS-side config values so mismatches are easy to spot.
 *
 * Usage:
 *   php artisan sso:health
 *   php artisan sso:health --json     (raw JSON from SSO health endpoint)
 */
class CheckSsoHealth extends Command
{
    private const NOT_SET = '<comment>not set</>';

    protected $signature   = 'sso:health {--json : Output raw JSON response from SSO}';
    protected $description = 'Check SSO server health and LUCAS SSO configuration';

    public function handle(): int
    {
        $ssoUrl = rtrim(config('sso.url'), '/');

        if (!$ssoUrl) {
            $this->error('SSO_URL is not configured. Add it to your .env file.');
            return self::FAILURE;
        }

        $this->showLucasConfig($ssoUrl);

        try {
            [$body, $data] = $this->callSsoHealth($ssoUrl);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line($body);
        } else {
            $this->displayHealthReport($data, $ssoUrl);
        }

        return self::SUCCESS;
    }

    private function showLucasConfig(string $ssoUrl): void
    {
        $this->line('');
        $this->line('<options=bold>LUCAS SSO Configuration</>');
        $this->table(['Key', 'Value'], [
            ['SSO_URL',            $ssoUrl],
            ['SSO_ISSUER',         config('sso.issuer')      ?? self::NOT_SET],
            ['SSO_SYSTEM_CODE',    config('sso.system_code') ?? self::NOT_SET],
            ['SSO_WEBHOOK_SECRET', config('sso.webhook_secret') ? '<info>set</>' : self::NOT_SET],
            ['Public key',         $this->resolveKeySource()],
        ]);
    }

    /**
     * Fetch and validate the SSO health endpoint.
     *
     * @return array{0: string, 1: array<string, mixed>}
     * @throws \RuntimeException on connection failure, non-200 response, or invalid JSON.
     */
    private function callSsoHealth(string $ssoUrl): array
    {
        $healthUrl = "{$ssoUrl}/sso-health-check";
        $this->line('');
        $this->line("Contacting <options=bold>{$healthUrl}</> ...");

        $ch = curl_init($healthUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $this->line('');

        if ($curlErr) {
            throw new SsoServerUnreachableException($curlErr);
        }

        if ($httpCode !== 200) {
            throw new SsoUnexpectedHttpStatusException($httpCode);
        }

        $data = json_decode($body, true);

        if ($data === null) {
            throw new SsoInvalidJsonResponseException();
        }

        return [$body, $data];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function displayHealthReport(array $data, string $ssoUrl): void
    {
        $status = strtoupper($data['status'] ?? 'unknown');
        $this->line("<options=bold>SSO Server Status: <info>{$status}</></>");

        $this->table(['Field', 'Value'], [
            ['Status',               $this->badge($data['status'] ?? 'unknown')],
            ['Timestamp',            $data['timestamp']  ?? '—'],
            ['App URL',              $data['app_url']    ?? '—'],
            ['Database',             $this->badge($data['database'] ?? 'unknown', 'ok')],
            ['Public key files',     ($data['public_key_configured'] ?? false) ? '<info>configured</>' : '<error>MISSING</>'],
            ['Users (total/active)', ($data['users']['total'] ?? 0) . ' / ' . ($data['users']['active'] ?? 0)],
        ]);

        $systems = $data['registered_systems'] ?? [];
        if ($systems) {
            $this->line('<options=bold>Registered Systems</>');
            $this->table(['Code', 'Name', 'Status'], array_map(
                fn($s) => [$s['code'], $s['name'], $this->badge($s['status'], 'active')],
                $systems
            ));
        } else {
            $this->warn('No registered systems found on SSO.');
        }

        // ── Warn on obvious mismatches ───────────────────────────────────────
        $lucasCode   = config('sso.system_code');
        $activeCodes = array_column($systems, 'code');
        if ($lucasCode && !in_array($lucasCode, $activeCodes, true)) {
            $this->warn("SSO_SYSTEM_CODE \"{$lucasCode}\" is not in SSO's active system list.");
        }

        $ssoAppUrl = rtrim($data['app_url'] ?? '', '/');
        if ($ssoAppUrl && $ssoAppUrl !== $ssoUrl) {
            $this->warn("SSO_URL ({$ssoUrl}) differs from SSO's APP_URL ({$ssoAppUrl}).");
        }

        $this->line('');
    }

    /**
     * Describe how this LUCAS instance has its SSO public key configured.
     */
    private function resolveKeySource(): string
    {
        if (config('sso.public_key')) {
            return '<info>env (SSO_PUBLIC_KEY)</>';
        }

        $path = config('sso.public_key_path');
        if ($path && file_exists($path)) {
            return "<info>file ({$path})</>";
        }

        return '<error>NOT CONFIGURED</>';
    }

    /**
     * Colour a status value green if it matches $good, red otherwise.
     *
     */
    private function badge(string $value, string $good = 'up'): string
    {
        return strtolower($value) === strtolower($good)
            ? "<info>{$value}</>"
            : "<error>{$value}</>";
    }
}
