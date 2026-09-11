<?php

namespace App\Services\UserSettings;

use App\Models\Branch;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Section;
use App\Models\User;

class UserManagementSyncPreparationService
{
    public function setBranchDealerArea($branchDealerUser)
    {
        if ($branchDealerUser) {
            if ($branchDealerUser->type === 'BRANCH') {
                $area = $branchDealerUser->branch ? $branchDealerUser->branch->name : 'N/A';
            } elseif ($branchDealerUser->type === 'DEALER') {
                $area = $branchDealerUser->dealer ? $branchDealerUser->dealer->name : 'N/A';
            } else {
                $area = 'N/A';
            }
        } else {
            $area = 'N/A';
        }

        return $area;
    }

    public function setHeadOfficeArea($headOfficeUser)
    {
        $parts = [
            $headOfficeUser->section->name ?? null,
            $headOfficeUser->department->name ?? null,
            $headOfficeUser->division->name ?? null,
            $headOfficeUser->group->name ?? null,
            $headOfficeUser->location ?? null,
        ];

        return implode('<br>', array_filter($parts));
    }

    public function checkPositionValidity($isAlternateUser)
    {
        return [
            'required',
            function ($attribute, $value, $fail) use ($isAlternateUser) {
                $normalized = strtoupper(trim($value));

                if ($isAlternateUser == 1 && $normalized !== 'ALTERNATE USER') {
                    $fail("{$attribute} must be ALTERNATE USER when the user is set as alternate user is checked.");
                }

                if ($isAlternateUser == 0 && $normalized === 'ALTERNATE USER') {
                    $fail("{$attribute} cannot be ALTERNATE USER when the user is not checked to be alternate user.");
                }
            }
        ];
    }

    public function checkAlternateUserValidity($alternateUserParams, $userId = null)
    {
        return [
            'required',
            function ($attribute, $value, $fail) use ($alternateUserParams, $userId) {
                $this->inspectAlternateUser($attribute, $value, $fail, $alternateUserParams, $userId);
            }
        ];
    }

    public function inspectAlternateUser($attribute, $value, $fail, $alternateUserParams, $userId)
    {
        if ((int) $value !== 1) {
            return;
        }

        $query = $this->buildAlternateUserQuery($alternateUserParams, $value);

        if ($userId) {
            $query->where('id', '!=', $userId);
        }

        if ($query->exists()) {
            $fail($this->getAlternateUserErrorMessage($alternateUserParams));
        }
    }

    public function buildAlternateUserQuery($params, $value)
    {
        $query = User::where('isAlternateUser', $value)
            ->where('company', $params['company']);

        if ((int) $params['isBranchDealer'] === 1) {
            return $this->applyBranchDealerFilter($query, $params);
        }

        return $this->applyHeadOfficeFilter($query, $params);
    }

    public function applyBranchDealerFilter($query, $params)
    {
        $isDealer = $params['company'] === 'DEALER';

        $model = $isDealer ? Dealer::class : Branch::class;
        $type = $isDealer ? 'DEALER' : 'BRANCH';

        $branchDealerId = $model::where('name', $params['branchDealerId'])->value('id');

        return $query->whereHas('branchDealerUser', function ($q) use ($branchDealerId, $type) {
            $q->where('type', $type)
                ->where('branch_id', $branchDealerId);
        });
    }

    public function applyHeadOfficeFilter($query, $params)
    {
        $organizationData = $this->getHeadOfficeOrganizationName($params['organization']);
        [$group_id, $division_id, $department_id, $section_id] =
            $this->getHeadOfficeOrganizationId($organizationData);

        return $query->whereHas('headOfficeUser', function ($q) use (
            $group_id,
            $division_id,
            $department_id,
            $section_id
        ) {
            $q->where('group_id', $group_id)
                ->where('division_id', $division_id)
                ->where('department_id', $department_id)
                ->where('section_id', $section_id);
        });
    }

    public function getAlternateUserErrorMessage($params)
    {
        return (int) $params['isBranchDealer'] === 1
            ? 'There is already an Alternate User set to this Branch.'
            : 'There is already an Alternate User set to this office organization.';
    }

    public function getHeadOfficeOrganizationName($organizationData)
    {
        $splittedText = explode("->", $organizationData);

        return end($splittedText);
    }

    public function getHeadOfficeOrganizationId($organization)
    {
        $models = [
            'group' => Group::class,
            'division' => Division::class,
            'department' => Department::class,
            'section' => Section::class,
        ];

        $group_id = null;
        $division_id = null;
        $department_id = null;
        $section_id = null;

        foreach ($models as $type => $model) {
            $data = $model::where('name', $organization)->first();

            if ($data) {
                switch ($type) {
                    case 'group':
                        $group_id = $data->id;
                        break;
                    case 'division':
                        $group_id = $data->group?->id ?? null;
                        $division_id = $data->id;
                        break;
                    case 'department':
                        $group_id = $data->group?->id ?? null;
                        $division_id = $data->division?->id ?? null;
                        $department_id = $data->id;
                        break;
                    case 'section':
                        $group_id = $data->group?->id ?? null;
                        $division_id = $data->division?->id ?? null;
                        $department_id = $data->department?->id ?? null;
                        $section_id = $data->id;
                        break;
                    default:
                        $group_id = null;
                        $division_id = null;
                        $department_id = null;
                        $section_id = null;
                        break;
                }
                break;
            }
        }

        return [$group_id, $division_id, $department_id, $section_id];
    }

    public function prepareBranchDealerData($user)
    {
        if (!$user) {
            return 'N/A';
        }

        if ($user->type === 'DEALER') {
            $branchDealerId = $user->dealer->name ?? 'N/A';
        } else {
            $branchDealerId = $user->branch->name ?? 'N/A';
        }

        return $branchDealerId;
    }

    public function prepareHeadOfficeOrganizationData($user)
    {
        $organizationDetails = [
            'group' => $user->group,
            'division' => $user->division,
            'department' => $user->department,
            'section' => $user->section,
        ];

        $lastModel = null;

        foreach ($organizationDetails as $org => $detail) {
            if (!empty($detail)) {
                $lastModel = $org;
            }
        }

        switch ($lastModel) {
            case 'section':
                $organization = $this->buildName([$user->group, $user->division, $user->department], $user->section);
                break;
            case 'department':
                $organization = $this->buildName([$user->group, $user->division], $user->department);
                break;
            case 'division':
                $organization = $this->buildName([$user->group], $user->division);
                break;
            case 'group':
                $organization = $user->group->name;
                break;
            default:
                $organization = null;
        }

        return $organization;
    }

    public function buildName(array $hierarchy, $item)
    {
        $transformedCode = collect($hierarchy)
            ->filter()
            ->pluck('code')
            ->implode('->');

        return ($transformedCode ? $transformedCode . '->' : '') . $item->name;
    }
}
