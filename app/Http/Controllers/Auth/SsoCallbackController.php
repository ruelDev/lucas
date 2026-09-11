<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Section;
use App\Models\User;
use App\Models\UserHasBranchDealer;
use App\Models\UserHasHeadOffice;
use App\Jobs\ConsumeSsoTokenJob;
use App\Services\SsoOfficeSyncService;
use App\Services\SsoVerificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Spatie\Permission\Models\Role;

class SsoCallbackController extends Controller
{
    private const AUDIT_MODULE            = 'User Management';
    private const AUDIT_AGENT             = 'SSO Sync';
    private const ERR_MISSING_EMPLOYEE_ID = 'Missing employee_id.';

    public function __construct(
        private SsoVerificationService $sso,
        private SsoOfficeSyncService $officeSync,
    ) {}

    // -------------------------------------------------------------------------
    // SSO Callback — GET /sso/callback?sso_token=<jwt>
    // -------------------------------------------------------------------------
    public function callback(Request $request): RedirectResponse
    {
        $token = $request->query('sso_token');
        Log::channel('sso')->info('SSO callback reached', ['has_token' => !empty($token), 'ip' => $request->ip()]);
        $this->logoutExistingSession($request);

        if (!$token) {
            return redirect()->route('login')->withErrors(['sso' => 'SSO token missing.']);
        }

        [$claims, $user, $error] = $this->authenticateViaToken($request, $token);
        if ($error) {
            return $error;
        }

        $this->completeLogin($request, $user, $claims);
        return redirect()->intended(route('dashboard'));
    }

    // -------------------------------------------------------------------------
    // Logout Webhook — POST /sso/logout-webhook
    // -------------------------------------------------------------------------
    public function logoutWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-SSO-Signature', '');
        $timestamp = $request->header('X-SSO-Timestamp', '');

        try {
            $this->sso->verifyWebhookSignature($rawBody, $signature, $timestamp);
        } catch (\RuntimeException $e) {
            Log::channel('sso')->warning('SSO logout webhook rejected: ' . $e->getMessage(), ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $data       = json_decode($rawBody, true);
        $employeeId = $data['employee_id'] ?? null;

        if (!$employeeId) {
            return response()->json(['error' => self::ERR_MISSING_EMPLOYEE_ID], 422);
        }

        $user = User::where('employee_id', $employeeId)->first();

        if ($user) {
            $this->killUserSessions($user->id);
            AuditLog::create([
                'user_id'    => $user->id,
                'event'      => 'SSO Logout',
                'module'     => 'Authentication',
                'new_data'   => json_encode(['employee_id' => $employeeId, 'reason' => $data['reason'] ?? 'logout']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            Log::channel('sso')->info("SSO logout webhook: terminated sessions for [{$employeeId}].");
        }

        return response()->json(['message' => 'Session terminated.']);
    }

    // -------------------------------------------------------------------------
    // User sync webhook — POST /sso/user-sync
    // -------------------------------------------------------------------------
    public function userSyncWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-SSO-Signature', '');
        $timestamp = $request->header('X-SSO-Timestamp', '');

        try {
            $this->sso->verifyWebhookSignature($rawBody, $signature, $timestamp);
        } catch (\RuntimeException $e) {
            Log::channel('sso')->warning('SSO user-sync webhook rejected: ' . $e->getMessage(), ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $data       = json_decode($rawBody, true);
        $employeeId = $data['employee_id'] ?? null;

        if (!$employeeId) {
            return response()->json(['error' => self::ERR_MISSING_EMPLOYEE_ID], 422);
        }

        return $this->processSyncUser($employeeId, $data, $request);
    }

    // -------------------------------------------------------------------------
    // Check user transactions webhook — POST /sso/check-user-transactions
    // -------------------------------------------------------------------------
    public function checkUserTransactionsWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-SSO-Signature', '');
        $timestamp = $request->header('X-SSO-Timestamp', '');

        try {
            $this->sso->verifyWebhookSignature($rawBody, $signature, $timestamp);
        } catch (\RuntimeException $e) {
            Log::channel('sso')->warning('SSO check-user-transactions webhook rejected: ' . $e->getMessage(), ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $data       = json_decode($rawBody, true);
        $employeeId = $data['employee_id'] ?? null;

        if (!$employeeId) {
            return response()->json(['error' => self::ERR_MISSING_EMPLOYEE_ID], 422);
        }

        $user            = User::where('employee_id', $employeeId)->first();
        $hasTransactions = $user && $this->userHasTransactions($user);

        return response()->json([
            'can_delete' => !$hasTransactions,
            'message'    => $hasTransactions
                ? 'User has existing transactions and cannot be deleted.'
                : 'User has no transactions and can be deleted.',
        ]);
    }

    // -------------------------------------------------------------------------
    // User delete webhook — POST /sso/user-delete
    // -------------------------------------------------------------------------
    public function userDeleteWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-SSO-Signature', '');
        $timestamp = $request->header('X-SSO-Timestamp', '');

        try {
            $this->sso->verifyWebhookSignature($rawBody, $signature, $timestamp);
        } catch (\RuntimeException $e) {
            Log::channel('sso')->warning('SSO user-delete webhook rejected: ' . $e->getMessage(), ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $data       = json_decode($rawBody, true);
        $employeeId = $data['employee_id'] ?? null;

        if (!$employeeId) {
            return response()->json(['error' => self::ERR_MISSING_EMPLOYEE_ID], 422);
        }

        return $this->processDeleteUser($employeeId, $data, $request);
    }

    // -------------------------------------------------------------------------
    // Audit log webhook — POST /sso/audit-log
    // -------------------------------------------------------------------------
    public function auditLogWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-SSO-Signature', '');
        $timestamp = $request->header('X-SSO-Timestamp', '');

        try {
            $this->sso->verifyWebhookSignature($rawBody, $signature, $timestamp);
        } catch (\RuntimeException $e) {
            Log::channel('sso')->warning('SSO audit-log webhook rejected: ' . $e->getMessage(), ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $data       = json_decode($rawBody, true);
        $employeeId = $data['employee_id'] ?? null;

        if (!$employeeId) {
            return response()->json(['error' => self::ERR_MISSING_EMPLOYEE_ID], 422);
        }

        $user = \App\Models\User::where('employee_id', $employeeId)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        \App\Models\AuditLog::create([
            'user_id'    => $user->id,
            'event'      => $data['event'] ?? 'Unknown',
            'model'      => 'User',
            'module'     => $data['module'] ?? 'Authentication',
            'old_data'   => isset($data['old_data']) ? json_encode($data['old_data']) : null,
            'new_data'   => isset($data['new_data']) ? json_encode($data['new_data']) : null,
            'ip_address' => $data['ip_address'] ?? $request->ip(),
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    // -------------------------------------------------------------------------
    // Organization sync webhook — SSO pushes a Branch/Dealer/Group/Division/
    // Department/Section create/update/delete here so both apps' org tables
    // stay consistent, mirroring the outbound OrgSyncClient::pushToSso().
    // A plain Eloquent write — never triggers an outbound push itself, so
    // there's no create->sync->create ping-pong between the two apps.
    // POST /sso/org-sync-webhook
    // -------------------------------------------------------------------------
    public function orgSyncWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-SSO-Signature', '');
        $timestamp = $request->header('X-SSO-Timestamp', '');

        try {
            $this->sso->verifyWebhookSignature($rawBody, $signature, $timestamp);
        } catch (\RuntimeException $e) {
            Log::channel('sso')->warning('SSO org-sync webhook rejected: ' . $e->getMessage(), ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $data         = json_decode($rawBody, true);
        $type         = $data['type'] ?? null;
        $action       = $data['action'] ?? null;
        $code         = $data['code'] ?? null;
        $previousCode = $data['previous_code'] ?? null;

        if (!$type || !$action || !$code) {
            return response()->json(['error' => 'Missing type/action/code.'], 422);
        }

        $modelClass = $this->orgModelClass($type);
        $codeColumn = $this->orgCodeColumn($type);
        $existing   = $this->findOrgEntity($modelClass, $codeColumn, $code, $previousCode);

        if ($action === 'delete') {
            if ($existing && !$existing->trashed()) {
                if ($blockedBy = $this->orgDeleteBlockedBy($type, $existing)) {
                    return response()->json([
                        'error' => "Cannot delete {$type} on LUCAS — it still has {$blockedBy} referencing it.",
                    ], 422);
                }
                $existing->delete();
            }
            return response()->json(['message' => 'Org sync delete processed.']);
        }

        // Include the (possibly renamed) code in the attributes applied on both the create
        // and the update path, so a rename that DID find its row via previous_code actually
        // takes effect here instead of leaving the row's code untouched forever.
        $attributes = [$codeColumn => $code] + $this->buildOrgAttributes($type, $data);

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($attributes);
        } else {
            $modelClass::create($attributes);
        }

        return response()->json(['message' => 'Org sync applied.']);
    }

    /**
     * Finds the local row for an incoming org-sync request. Looks up by the (possibly new)
     * `code` first — the common case, including create/delete/no-rename updates. If that
     * misses and the caller sent `previous_code` (meaning this is an update whose code just
     * changed), falls back to a lookup by the OLD code so the existing row is found and
     * updated in place instead of a duplicate being created. See SSO's docs/SSO.md,
     * "Organization sync — renamed codes".
     */
    private function findOrgEntity(string $modelClass, string $codeColumn, string $code, ?string $previousCode)
    {
        $existing = $modelClass::withTrashed()->where($codeColumn, $code)->first();

        if (!$existing && $previousCode) {
            $existing = $modelClass::withTrashed()->where($codeColumn, $previousCode)->first();
        }

        return $existing;
    }

    private function orgModelClass(string $type): string
    {
        return match ($type) {
            'BRANCH'     => Branch::class,
            'DEALER'     => Dealer::class,
            'GROUP'      => Group::class,
            'DIVISION'   => Division::class,
            'DEPARTMENT' => Department::class,
            'SECTION'    => Section::class,
        };
    }

    private function orgCodeColumn(string $type): string
    {
        return match ($type) {
            'BRANCH' => 'branch_code',
            'DEALER' => 'dealer_code',
            default  => 'code',
        };
    }

    /**
     * Mirrors each entity's own UserSettings\*Controller::destroy() guard, so
     * a sync-triggered delete can't blow past the same cross-hierarchy
     * protection a direct admin delete already respects on this side.
     *
     * @return string|null a human-readable description of what's blocking, or null if clear
     */
    private function orgDeleteBlockedBy(string $type, $model): ?string
    {
        return match ($type) {
            'BRANCH' => $model->users()->exists() ? 'users' : null,
            'DEALER' => $model->users()->exists() ? 'users' : null,
            'GROUP' => ($model->headOfficeUser()->exists() || $model->division()->exists() || $model->department()->exists() || $model->section()->exists())
                ? 'users or offices' : null,
            'DIVISION' => ($model->headOfficeUser()->exists() || $model->department()->exists() || $model->section()->exists())
                ? 'users or offices' : null,
            'DEPARTMENT' => ($model->headOfficeUser()->exists() || $model->section()->exists())
                ? 'users or offices' : null,
            'SECTION' => $model->headOfficeUser()->exists() ? 'users' : null,
        };
    }

    private function buildOrgAttributes(string $type, array $data): array
    {
        $attributes = [
            'name'    => $data['name'] ?? null,
            'status'  => $data['status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null,
        ];

        if (in_array($type, ['BRANCH', 'DEALER'])) {
            $attributes['location'] = $data['location'] ?? '';
        }

        if ($type === 'DEALER') {
            $attributes['bank_account_name'] = $data['bank_account_name'] ?? null;
            $attributes['bank_account']      = $data['bank_account'] ?? null;
        }

        if (in_array($type, ['GROUP', 'DIVISION', 'DEPARTMENT', 'SECTION'])) {
            $attributes['description'] = $data['description'] ?? '';
        }

        if (in_array($type, ['DIVISION', 'DEPARTMENT', 'SECTION'])) {
            $groupCode = $data['group_code'] ?? null;
            $attributes['group_id'] = $groupCode ? Group::where('code', $groupCode)->value('id') : null;
        }

        if (in_array($type, ['DEPARTMENT', 'SECTION'])) {
            $divisionCode = $data['division_code'] ?? null;
            $attributes['division_id'] = $divisionCode ? Division::where('code', $divisionCode)->value('id') : null;
        }

        if ($type === 'SECTION') {
            $departmentCode = $data['department_code'] ?? null;
            $attributes['department_id'] = $departmentCode ? Department::where('code', $departmentCode)->value('id') : null;
        }

        return $attributes;
    }

    // =========================================================================
    // Callback helpers
    // =========================================================================

    private function logoutExistingSession(Request $request): void
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    private function authenticateViaToken(Request $request, string $token): array
    {
        [$claims, $error] = $this->verifyToken($request, $token);
        if ($error) {
            return [null, null, $error];
        }

        [$user, $error] = $this->findOrProvisionUser($claims['employee_id'], $claims);
        if ($error) {
            return [null, null, $error];
        }

        return [$claims, $user, null];
    }

    private function verifyToken(Request $request, string $token): array
    {
        try {
            $claims = $this->sso->verify($token);
            Log::channel('sso')->info('SSO token verified', ['employee_id' => $claims['employee_id'] ?? null]);
        } catch (\Throwable $e) {
            return [null, $this->buildTokenErrorRedirect($request, $e)];
        }

        if (empty($claims['employee_id'])) {
            Log::warning('SSO callback: no employee_id in claims');
            return [null, redirect()->route('login')->withErrors(['sso' => 'Invalid SSO token claims.'])];
        }

        return [$claims, null];
    }

    private function buildTokenErrorRedirect(Request $request, \Throwable $e): RedirectResponse
    {
        Log::channel('sso')->warning('SSO callback token verification failed: ' . $e->getMessage(), [
            'ip'                => $request->ip(),
            'exception'         => get_class($e),
            'sso_issuer_config' => config('sso.issuer'),
            'sso_url_config'    => config('sso.url'),
        ]);

        $ssoUrl = config('sso.url');
        if ($ssoUrl) {
            return redirect(rtrim($ssoUrl, '/') . '/dashboard?' . http_build_query(['sso_error' => 'token_mismatch']));
        }

        return redirect()->route('login')->withErrors(['sso' => 'SSO authentication failed: ' . $e->getMessage()]);
    }

    private function findOrProvisionUser(string $employeeId, array $claims): array
    {
        $user = User::withTrashed()->where('employee_id', $employeeId)->first();

        if ($user && $user->trashed()) {
            Log::channel('sso')->warning("SSO callback: user [{$employeeId}] has been removed from LUCAS.");
            return [null, redirect()->route('login')->withErrors([
                'sso' => 'Your LUCAS account has been removed. Please contact your administrator.',
            ])];
        }

        $user = $user ?? $this->autoProvisionUser($employeeId, $claims);

        if (!$user) {
            Log::channel('sso')->warning("SSO callback: user [{$employeeId}] not found and JWT has no profile fields.");
            return [null, redirect()->route('login')->withErrors([
                'sso' => 'Your account is not registered in this system. Please contact your administrator.',
            ])];
        }

        Log::channel('sso')->info('SSO callback: user found', ['user_id' => $user->id, 'status' => $user->status]);

        if (!$user->isActive()) {
            Log::channel('sso')->warning("SSO callback: user [{$employeeId}] is not active.");
            return [null, redirect()->route('login')->withErrors(['sso' => 'Your LUCAS account is inactive or locked.'])];
        }

        return [$user, null];
    }

    private function autoProvisionUser(string $employeeId, array $claims): ?User
    {
        if (empty($claims['fname']) || empty($claims['lname'])) {
            return null;
        }

        $user = User::create([
            'employee_id'     => $employeeId,
            'fname'           => $claims['fname'],
            'mname'           => $claims['mname'] ?? null,
            'lname'           => $claims['lname'],
            'email'           => $claims['email'] ?? null,
            'company'         => $claims['company'] ?? 'N/A',
            'position'        => $claims['position'] ?? 'N/A',
            'status'          => 'active',
            'isReset'         => $claims['isReset'] ?? 1,
            'isBranchDealer'  => $claims['isBranchDealer'] ?? 0,
            'expiration_date' => $claims['expiration_date'] ? Carbon::parse($claims['expiration_date'])->format('Y-m-d') : null,
            'profile_picture' => $claims['profile_picture'] ?? null,
            'password'        => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(64)),
        ]);

        Log::channel('sso')->info("SSO callback: auto-provisioned new LUCAS user [{$employeeId}] from JWT claims.", [
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function completeLogin(Request $request, User $user, array $claims): void
    {
        Log::channel('sso')->info('SSO callback: user is active, logging in');

        Auth::login($user, remember: false);
        $request->session()->regenerate();
        Redis::del("user:{$user->id}:last_seen");

        $ssoRoles = collect($claims['system_permissions']['roles'] ?? [])
            ->reject(fn ($r) => in_array($r, ['sso_admin', 'sso_user']))
            ->values()
            ->toArray();
        foreach ($ssoRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        $user->syncRoles($ssoRoles);

        if (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        session(['sso_jti'          => $claims['jti']]);
        session(['sso_authenticated' => true]);
        session(['just_logged_in'    => true]);
        $user->increment('session_version');
        session(['sv' => $user->session_version]);

        // Report back to SSO that this handoff token has been used, so it can
        // mark it revoked (single-use). Best-effort/async — must never block
        // or fail the login; the actual replay guard is the local jti claim
        // in SsoVerificationService::verify(), not this notification landing.
        ConsumeSsoTokenJob::notifyOrQueue($claims['jti']);

        Log::channel('sso')->info('SSO callback: login complete, redirecting to dashboard', ['user_id' => $user->id]);

        AuditLog::create([
            'user_id'    => $user->id,
            'event'      => 'SSO Login',
            'module'     => 'Authentication',
            'new_data'   => json_encode(['employee_id' => $claims['employee_id'], 'sso_jti' => $claims['jti']]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    // =========================================================================
    // Webhook helpers
    // =========================================================================

    private function processSyncUser(string $employeeId, array $data, Request $request): JsonResponse
    {
        $rawPasswordHash      = !empty($data['hashed_password']) ? $data['hashed_password'] : null;
        $branchDealerId       = $data['branchDealerId'] ?? null;
        $location             = $data['location'] ?? null;
        $organizationCode     = !empty($data['organizationCode']) ? $data['organizationCode'] : null;
        $organizationCodeType = !empty($data['organizationCodeType']) ? $data['organizationCodeType'] : null;
        $incomingRole         = !empty($data['role']) ? trim($data['role']) : null;
        $actorId              = !empty($data['actor_employee_id'])
            ? User::where('employee_id', $data['actor_employee_id'])->value('id')
            : null;

        $toUpdate = array_filter([
            'fname'           => $data['fname'] ?? null,
            'mname'           => $data['mname'] ?? null,
            'lname'           => $data['lname'] ?? null,
            'email'           => $data['email'] ?? null,
            'company'         => $data['company'] ?? null,
            'position'        => $data['position'] ?? null,
            'status'          => $data['status'] ?? null,
            'isReset'         => $data['isReset'] ?? null,
            'isBranchDealer'  => $data['isBranchDealer'] ?? null,
            'expiration_date' => $data['expiration_date'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? null,
            'remarks'         => $data['remarks'] ?? null,
        ], fn($v) => !is_null($v));

        // SSO renamed a still-provisional user (one who never logged in). Carry the
        // rename onto our record so the rest of this method updates it in place
        // rather than creating a duplicate under the new employee_id.
        $this->applyEmployeeIdRename($data['previous_employee_id'] ?? null, $employeeId, $request);

        $user = User::withTrashed()->where('employee_id', $employeeId)->first();

        if (!$user) {
            $createData = array_merge($toUpdate, [
                'employee_id'    => $employeeId,
                'password'       => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(64)),
                'company'        => $toUpdate['company'] ?? 'N/A',
                'position'       => $toUpdate['position'] ?? 'N/A',
                'isBranchDealer' => $toUpdate['isBranchDealer'] ?? 0,
                'isReset'        => $toUpdate['isReset'] ?? 1,
            ]);
            $user = User::create($createData);
            $this->writePasswordHash($user, $rawPasswordHash);
            $this->officeSync->sync($user, $toUpdate['isBranchDealer'] ?? 0, $toUpdate['company'] ?? null, $branchDealerId, $location, $organizationCode, $organizationCodeType);
            $this->syncRoleIfPresent($user, $incomingRole);
            Log::channel('sso')->info('SSO user-sync webhook: new user created.', ['employee_id' => $employeeId, 'user_id' => $user->id]);
            AuditLog::create([
                'user_id'    => $actorId ?? $user->id,
                'event'      => 'CREATED',
                'module'     => self::AUDIT_MODULE,
                'model'      => 'User',
                'old_data'   => null,
                'new_data'   => json_encode($user->getAttributes()),
                'ip_address' => $request->ip(),
                'user_agent' => self::AUDIT_AGENT,
            ]);
            return response()->json(['message' => 'User created.']);
        }

        // Admin re-granted Lucas access to a previously soft-deleted user.
        // Restore the record so the SSO callback can find a non-trashed user
        // and allow the login to proceed normally.
        if ($user->trashed()) {
            $user->restore();
            if (!empty($toUpdate)) {
                $user->update($toUpdate);
            }
            $this->writePasswordHash($user, $rawPasswordHash);
            $this->officeSync->sync(
                $user,
                $toUpdate['isBranchDealer'] ?? $user->isBranchDealer,
                $toUpdate['company'] ?? $user->company,
                $branchDealerId, $location, $organizationCode, $organizationCodeType
            );
            $this->syncRoleIfPresent($user, $incomingRole);
            Log::channel('sso')->info('SSO user-sync webhook: restored soft-deleted user.', ['employee_id' => $employeeId, 'user_id' => $user->id]);
            AuditLog::create([
                'user_id'    => $actorId ?? $user->id,
                'event'      => 'RESTORED',
                'module'     => self::AUDIT_MODULE,
                'model'      => 'User',
                'old_data'   => null,
                'new_data'   => json_encode($user->fresh()->getAttributes()),
                'ip_address' => $request->ip(),
                'user_agent' => self::AUDIT_AGENT,
            ]);
            return response()->json(['message' => 'User restored.']);
        }

        $oldData = $user->getAttributes();
        if (!empty($toUpdate)) {
            $user->update($toUpdate);
        }
        $this->writePasswordHash($user, $rawPasswordHash);
        $this->officeSync->sync(
            $user,
            $toUpdate['isBranchDealer'] ?? $user->isBranchDealer,
            $toUpdate['company'] ?? $user->company,
            $branchDealerId, $location, $organizationCode, $organizationCodeType
        );
        $this->syncRoleIfPresent($user, $incomingRole);

        // If the sync is deactivating the user, invalidate any live session
        // immediately rather than waiting for the next SSO logout webhook.
        if (isset($toUpdate['status']) && $toUpdate['status'] !== 'active') {
            $this->killUserSessions($user->id);
        }

        Log::channel('sso')->info('SSO user-sync webhook applied.', ['employee_id' => $employeeId, 'fields' => array_keys($toUpdate)]);
        AuditLog::create([
            'user_id'    => $actorId ?? $user->id,
            'event'      => 'UPDATED',
            'module'     => self::AUDIT_MODULE,
            'model'      => 'User',
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($user->getAttributes()),
            'ip_address' => $request->ip(),
            'user_agent' => self::AUDIT_AGENT,
        ]);
        return response()->json(['message' => 'User synced.']);
    }

    /**
     * Rename our local user record when SSO reports a pre-first-login employee_id
     * change (`previous_employee_id`). No-op unless a record exists under the old id
     * and the new id is still free — otherwise the caller falls back to a normal
     * create/update keyed on the new id.
     */
    private function applyEmployeeIdRename(?string $previousEmployeeId, string $newEmployeeId, Request $request): void
    {
        if (empty($previousEmployeeId) || $previousEmployeeId === $newEmployeeId) {
            return;
        }

        $existing = User::withTrashed()->where('employee_id', $previousEmployeeId)->first();

        if (!$existing || User::withTrashed()->where('employee_id', $newEmployeeId)->exists()) {
            return;
        }

        $oldData = $existing->getAttributes();
        $existing->update(['employee_id' => $newEmployeeId]);

        Log::channel('sso')->info('SSO user-sync webhook: employee_id renamed.', [
            'from' => $previousEmployeeId, 'to' => $newEmployeeId, 'user_id' => $existing->id,
        ]);
        AuditLog::create([
            'user_id'    => $existing->id,
            'event'      => 'UPDATED',
            'module'     => self::AUDIT_MODULE,
            'model'      => 'User',
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($existing->getAttributes()),
            'ip_address' => $request->ip(),
            'user_agent' => self::AUDIT_AGENT,
        ]);
    }

    private function userHasTransactions(User $user): bool
    {
        foreach ($user->auditLogs()->where('user_id', $user->id)->get() as $log) {
            $oldData      = json_decode($log->old_data, true);
            $newData      = json_decode($log->new_data, true);
            $targetUserId = $oldData['id'] ?? $newData['id'] ?? null;

            if (!in_array($log->module, ['Session', 'Authentication']) && ($log->module !== 'User Management' || $targetUserId != $user->id)) {
                return true;
            }
        }

        return false;
    }

    private function processDeleteUser(string $employeeId, array $data, Request $request): JsonResponse
    {
        $actorId = !empty($data['actor_employee_id'])
            ? User::where('employee_id', $data['actor_employee_id'])->value('id')
            : null;

        $user = User::where('employee_id', $employeeId)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found — nothing to delete.']);
        }

        $oldData = $user->getAttributes();
        $userId  = $user->id;

        DB::transaction(function () use ($user, $userId, $actorId, $oldData, $request) {
            $this->killUserSessions($userId);

            if ($user->isBranchDealer == '1') {
                UserHasBranchDealer::where('user_id', $userId)->delete();
            } elseif ($user->isBranchDealer == '2') {
                UserHasHeadOffice::where('user_id', $userId)->delete();
            }

            $user->delete();

            AuditLog::create([
                'user_id'    => $actorId ?? $userId,
                'event'      => 'DELETED',
                'module'     => self::AUDIT_MODULE,
                'model'      => 'User',
                'old_data'   => json_encode($oldData),
                'new_data'   => null,
                'ip_address' => $request->ip(),
                'user_agent' => self::AUDIT_AGENT,
            ]);
        });

        Log::channel('sso')->info("SSO user-delete webhook: soft-deleted user [{$employeeId}].");
        return response()->json(['message' => 'User deleted.']);
    }

    private function writePasswordHash(User $user, ?string $rawPasswordHash): void
    {
        if ($rawPasswordHash) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => $rawPasswordHash]);
        }
    }

    private function syncRoleIfPresent(User $user, ?string $roleName): void
    {
        if (!$roleName) {
            return;
        }
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->syncRoles([$role]);
    }

    /**
     * Invalidate all active sessions for the given user by bumping their session_version.
     * The EnsureSessionVersionIsValid middleware will reject any request whose
     * session sv value no longer matches the stored version.
     */
    private function killUserSessions(int $userId): void
    {
        try {
            User::where('id', $userId)->update(['session_version' => 0]);
        } catch (\Throwable $e) {
            Log::channel('sso')->warning('Could not invalidate sessions for user ' . $userId . ': ' . $e->getMessage());
        }
    }

}
