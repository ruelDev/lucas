<?php

namespace App\Http\Controllers\UserSettings\Concerns;

use App\Services\OrgSyncClient;
use Illuminate\Support\Facades\Auth;

/**
 * Pushes a Branch/Dealer/Group/Division/Department/Section change to SSO
 * via OrgSyncClient's sync-or-queue delivery (see that class's docblock).
 * Never throws, so callers just call this after their model write — no
 * DB::transaction() wrapper needed, since each of these controllers only
 * ever writes the one row.
 *
 * $employee_id used to be a required parameter, but almost every call site
 * across these controllers omitted it — which threw an ArgumentCountError
 * right after the local DB write succeeded, before pushToSso() ever ran.
 * That exception was then swallowed by the caller's own try/catch, showing
 * the admin a false "Failed to ..." error while the local change had
 * actually gone through, AND silently skipping the push to SSO entirely
 * (so the change never reached SSO, and SSO never logged it). Defaulting
 * to the current admin's employee_id here fixes every call site at once.
 */
trait SyncsOrganizationChanges
{
    protected function syncOrgChange(string $type, string $action, array $payload, ?string $employee_id = null): void
    {
        $employee_id = $employee_id ?? Auth::user()?->employee_id;

        app(OrgSyncClient::class)->pushToSso($type, $action, $payload, $employee_id);
    }
}
