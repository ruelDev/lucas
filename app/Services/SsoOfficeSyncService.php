<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Section;
use App\Models\User;
use App\Models\UserHasBranchDealer;
use App\Models\UserHasHeadOffice;
use Illuminate\Support\Facades\Log;

class SsoOfficeSyncService
{
    public function sync(
        User $user,
        mixed $isBranchDealer,
        ?string $company,
        ?string $branchDealerId,
        ?string $location,
        ?string $organizationCode = null,
        ?string $organizationCodeType = null,
    ): void {
        $isBranchDealer = (int) $isBranchDealer;

        if ($isBranchDealer === 1 && $branchDealerId) {
            $this->syncBranchDealer($user, $company, $branchDealerId);
        } elseif ($isBranchDealer === 2 && $location) {
            $this->syncHeadOffice($user, $location, $organizationCode, $organizationCodeType);
        }
    }

    private function syncBranchDealer(User $user, ?string $company, string $branchDealerName): void
    {
        if ($company === 'DEALER') {
            $record = Dealer::where('name', $branchDealerName)->first();
            $type   = 'DEALER';
        } else {
            $record = Branch::where('name', $branchDealerName)->first();
            $type   = 'BRANCH';
        }

        if (!$record) {
            Log::channel('sso')->warning('SSO user-sync: branch/dealer not found by name.', [
                'name' => $branchDealerName,
                'type' => $type,
            ]);
            return;
        }

        $existing = UserHasBranchDealer::withTrashed()->where('user_id', $user->id)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update(['type' => $type, 'branch_id' => $record->id]);
        } else {
            UserHasBranchDealer::create([
                'user_id'   => $user->id,
                'type'      => $type,
                'branch_id' => $record->id,
            ]);
        }
    }

    private function syncHeadOffice(
        User $user,
        string $location,
        ?string $orgCode = null,
        ?string $orgType = null,
    ): void {
        $payload = ['location' => $location];

        if ($orgCode && $orgType) {
            $orgFields = $this->resolveOrgFields($orgCode, strtoupper($orgType));
            if ($orgFields) {
                $payload = array_merge($payload, $orgFields);
            }
        }

        $headOffice = UserHasHeadOffice::withTrashed()->where('user_id', $user->id)->first();

        if ($headOffice) {
            if ($headOffice->trashed()) {
                $headOffice->restore();
            }
            $headOffice->update($payload);
        } else {
            UserHasHeadOffice::create(array_merge($payload, ['user_id' => $user->id]));
        }
    }

    private function resolveOrgFields(string $code, string $type): ?array
    {
        $modelMap = [
            'GROUP'      => Group::class,
            'DIVISION'   => Division::class,
            'DEPARTMENT' => Department::class,
            'SECTION'    => Section::class,
        ];

        if (!isset($modelMap[$type])) {
            Log::channel('sso')->warning('SSO user-sync: unknown org type.', ['type' => $type, 'code' => $code]);
            return null;
        }

        $record = $modelMap[$type]::where('code', $code)->first();
        if (!$record) {
            return null;
        }

        return $this->buildOrgPayload($type, $record);
    }

    private function buildOrgPayload(string $type, mixed $record): array
    {
        return match ($type) {
            'GROUP'      => ['group_id' => $record->id,       'division_id' => null,                'department_id' => null,                    'section_id' => null],
            'DIVISION'   => ['group_id' => $record->group_id, 'division_id' => $record->id,          'department_id' => null,                    'section_id' => null],
            'DEPARTMENT' => ['group_id' => $record->group_id, 'division_id' => $record->division_id, 'department_id' => $record->id,             'section_id' => null],
            'SECTION'    => ['group_id' => $record->group_id, 'division_id' => $record->division_id, 'department_id' => $record->department_id,  'section_id' => $record->id],
            default      => [],
        };
    }
}
