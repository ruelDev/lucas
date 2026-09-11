<?php

namespace App\Support;

/**
 * Shared curl option builder for every outbound HTTP call LUCAS makes to
 * SSO. Centralizes TLS verification so it's configurable in one place
 * (config('sso.verify_ssl')/config('sso.ssl_ca_bundle')) instead of
 * repeated per call site.
 */
class SsoCurlOptions
{
    public static function build(array $extra = []): array
    {
        $options = [
            CURLOPT_TIMEOUT   => 5,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
        ];

        if (config('sso.verify_ssl') === false) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        } elseif ($caBundle = config('sso.ssl_ca_bundle')) {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        // $extra first so per-call options (POST fields, headers, etc.) win on
        // any key collision with these defaults.
        return $extra + $options;
    }
}
