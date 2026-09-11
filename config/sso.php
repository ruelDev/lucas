<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Server URL
    |--------------------------------------------------------------------------
    | The base URL of the SSO server. Used for webhook calls and the public-key
    | bootstrap command. Never fetched at request-time for token verification.
    */
    'url' => env('SSO_URL', 'http://localhost:8001'),

    /*
    |--------------------------------------------------------------------------
    | RSA Public Key
    |--------------------------------------------------------------------------
    | The SSO server's RSA public key in PEM format. Stored as an env variable
    | or as a file at the path below. LUCAS uses this to verify JWT signatures
    | locally — no network call is required per request.
    |
    | To bootstrap: php artisan sso:fetch-public-key
    */
    'public_key'      => env('SSO_PUBLIC_KEY'),         // PEM string (preferred)
    'public_key_path' => env('SSO_PUBLIC_KEY_PATH', storage_path('sso-public.key')), // fallback file

    /*
    |--------------------------------------------------------------------------
    | System Identity
    |--------------------------------------------------------------------------
    | The system code must match the `code` registered in the SSO systems table.
    | client_id / client_secret are used for SSO API calls (e.g. /api/sso/verify).
    */
    'system_code'   => env('SSO_SYSTEM_CODE', 'lucas'),
    'client_id'     => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Logout Webhook Secret
    |--------------------------------------------------------------------------
    | LUCAS verifies incoming logout webhooks with HMAC-SHA256. The secret must
    | match the SSO's system client_secret for this system.
    */
    'webhook_secret' => env('SSO_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Replay Protection
    |--------------------------------------------------------------------------
    | Maximum age (seconds) of an accepted logout webhook. Prevents replay attacks.
    */
    'webhook_max_age' => env('SSO_WEBHOOK_MAX_AGE', 60),

    /*
    |--------------------------------------------------------------------------
    | Idle Timeout
    |--------------------------------------------------------------------------
    | How long (in minutes) a user may be inactive in LUCAS before being
    | redirected back to SSO. Kept separate from SESSION_LIFETIME so the PHP
    | session (and Auth::check()) remains valid when the check fires.
    |
    | Production default: 120 min (matches SESSION_LIFETIME).
    | For testing a 1-min timeout: set SSO_IDLE_TIMEOUT=1 in .env while keeping
    | SESSION_LIFETIME at 120 so the session stays alive long enough to redirect.
    */
    'idle_timeout' => env('SSO_IDLE_TIMEOUT', 120),

    /*
    |--------------------------------------------------------------------------
    | Token Issuer
    |--------------------------------------------------------------------------
    | Expected `iss` claim in SSO-issued JWTs. Must match the SSO app URL.
    */
    'issuer' => env('SSO_ISSUER', env('SSO_URL', 'http://localhost:8001')),

    /*
    |--------------------------------------------------------------------------
    | Outbound HTTP (curl) TLS verification
    |--------------------------------------------------------------------------
    | Applies to every outbound call LUCAS makes to SSO (PushUserSyncToSsoJob,
    | PushUserDeleteToSsoJob, ConsumeSsoTokenJob, OrgSyncClient). Defaults to
    | real verification — only disable locally against a self-signed dev
    | cert, or point ssl_ca_bundle at a CA file if the system bundle itself
    | is the problem (common on Windows PHP without curl.cainfo configured).
    */
    'verify_ssl'    => env('SSO_VERIFY_SSL', true),
    'ssl_ca_bundle' => env('SSO_SSL_CA_BUNDLE'),
];
