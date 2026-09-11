<?php

namespace App\Services;

use App\Jobs\PushOrgSyncToSsoJob;

/**
 * Push of an organization-entity change to SSO.
 *
 * Sync-or-queue, the same reliability model as PushUserSyncToSsoJob/
 * PushUserDeleteToSsoJob: pushToSso() delegates to PushOrgSyncToSsoJob,
 * which tries a synchronous delivery and falls back to a queued retry on
 * ANY failure — unreachable or rejected. The caller's DB::transaction()
 * always commits regardless of SSO's reachability; see docs/LUCAS with
 * SSO.md §5d for the tradeoff this accepts (a genuinely rejected change,
 * e.g. a blocked delete, retries blindly and can silently fail after
 * exhausting retries).
 */
class OrgSyncClient
{
    public function pushToSso(string $type, string $action, array $payload, ?string $employee_id = null): void
    {
        PushOrgSyncToSsoJob::syncOrQueue($type, $action, $payload, $employee_id);
    }
}
