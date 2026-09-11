<?php

namespace App\Services\UserSettings;

use App\Models\Branch;
use App\Models\Dealer;
use App\Models\UserHasBranchDealer;
use App\Models\UserHasHeadOffice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementService
{
    protected $defaultRegexStructure = 'regex:/^[a-zA-Z\s\-]+$/';
    protected $userManagementSyncPreparationService;

    public function __construct(UserManagementSyncPreparationService $userManagementSyncPreparationService)
    {
        $this->userManagementSyncPreparationService = $userManagementSyncPreparationService;
    }

    public function prepareHeadOfficeUserOrBranchDealerUser($user)
    {
        if ($user->isBranchDealer == 1) {
            $branchDealerUser = $user->branchDealerUser;

            $area = $this->userManagementSyncPreparationService->setBranchDealerArea($branchDealerUser);
        } else {
            $headOfficeUser = $user->headOfficeUser;

            $area = $this->userManagementSyncPreparationService->setHeadOfficeArea($headOfficeUser);
        }

        return $area;
    }

    public function prepareBranchDealerType($user)
    {
        $branchId = null;
        $dealerId = null;
        $type = null;

        if ($user->branchDealerUser) {
            $type = $user->branchDealerUser->type;
            if ($type === 'BRANCH') {
                $branchId = $user->branchDealerUser->id;
            } elseif ($type === 'DEALER') {
                $dealerId = $user->branchDealerUser->id;
            }
        }

        return [
            $branchId,
            $dealerId,
            $type
        ];
    }

    public function validateNewUserBranchDealerOrOffice($validatedUserData, $request)
    {
        $validatedOfficeData = [];
        $validatedBranchDealerData = [];

        if ($validatedUserData['isBranchDealer'] == 1) {
            $validatedBranchDealerData = $request->validate([
                'branchDealerId' => 'required',
            ]);
        }

        if ($validatedUserData['isBranchDealer'] == 2) {
            $validatedOfficeData = $request->validate([
                'location' => 'required',
                'organization' => 'required',
            ]);
        }

        return [$validatedBranchDealerData, $validatedOfficeData];
    }

    public function validateNewUserData($request)
    {
        $alternateUserParams = [
            'position' => $request->position,
            'company' => $request->company,
            'isAlternateUser' => $request->isAlternateUser,
            'isBranchDealer' => $request->isBranchDealer,
            'branchDealerId' => $request->branchDealerId,
            'organization' => $request->organization,
        ];

        $validCompany = ['BMI', 'BFC', 'BOTH', 'DEALER'];

        $validatedUserData = $request->validate([
            'employee_id' => [
                'required',
                'digits:7',
                Rule::unique('users', 'employee_id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'fname' => ['required', $this->defaultRegexStructure],
            'mname' => ['nullable', $this->defaultRegexStructure],
            'lname' => ['required', $this->defaultRegexStructure],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'company' => ['required', Rule::in($validCompany)],
            'isAlternateUser' => $this->userManagementSyncPreparationService->checkAlternateUserValidity($alternateUserParams),
            'position' => $this->userManagementSyncPreparationService->checkPositionValidity($request->isAlternateUser),
            'expiration_date' => ['nullable', 'date', 'after:today'],
            'isBranchDealer' => ['required'],
            'role' => ['required'],
        ], [
            'fname.regex' => 'The first name may only contain letters, and hypens.',
            'mname.regex' => 'The middle name may only contain letters, and hypens.',
            'lname.regex' => 'The last name may only contain letters, and hypens.',
        ]);

        [$validatedBranchDealerData, $validatedOfficeData] = $this->validateNewUserBranchDealerOrOffice($validatedUserData, $request);

        $validatedUserData['fname'] = ucwords(strtolower($validatedUserData['fname']));
        $validatedUserData['mname'] = ucwords(strtolower($validatedUserData['mname']));
        $validatedUserData['lname'] = ucwords(strtolower($validatedUserData['lname']));

        $validatedUserData['password'] = "BMI-" . Str::password(6, true, true, false ,false);
        $validatedUserData['password_changed_at'] = Carbon::now();
        $validatedUserData['raw_password'] = $validatedUserData['password'];
        $validatedUserData['password'] = Hash::make($validatedUserData['password']);

        $validatedUserData['isReset'] = 0;
        $validatedUserData['status'] = 'active';
        $validatedUserData['title'] = 'Successful Account Creation';

        return [$validatedUserData, $validatedBranchDealerData, $validatedOfficeData];
    }

    public function syncNewUserBranchDealerOrOffice($user, $isBranchDealer, $validatedBranchData, $validatedOfficeData)
    {
        if ($isBranchDealer == 1) {
            if ($user->company === 'DEALER') {
                $branchDealerData = Dealer::select('id')->where('name', $validatedBranchData['branchDealerId'])->first();
            } else {
                $branchDealerData = Branch::select('id')->where('name', $validatedBranchData['branchDealerId'])->first();
            }

            UserHasBranchDealer::create([
                'user_id' => $user->id,
                'type' => ($user->company !== 'DEALER') ? 'BRANCH' : $user->company,
                'branch_id' => $branchDealerData->id
            ]);
        } else {
            $organizationData = $this->userManagementSyncPreparationService->getHeadOfficeOrganizationName($validatedOfficeData['organization']);
            [$group_id, $division_id, $department_id, $section_id] = $this->userManagementSyncPreparationService->getHeadOfficeOrganizationId($organizationData);

            UserHasHeadOffice::create([
                'user_id' => $user->id,
                'location' => $validatedOfficeData['location'],
                'group_id' => $group_id,
                'division_id' => $division_id,
                'department_id' => $department_id,
                'section_id' => $section_id,
            ]);
        }
    }

    public function validateExistingUserBranchOrOffice($isBranchDealer, $request)
    {
        $validatedBranchData = [];
        $validatedOfficeData = [];

        if ($isBranchDealer === 1) {
            $validatedBranchData = $request->validate([
                'branchDealerId' => 'required',
            ], [
                'branchDealerId.required' => 'This field is required.'
            ]);
        }

        if ($isBranchDealer === 2) {
            $validatedOfficeData = $request->validate([
                'location' => 'required',
                'organization' => 'required',
            ], [
                'location.required' => 'Location is required when location type is Head Office.',
                'organization.required' => 'Organization is required when office type is Head Office.',
            ]);
        }

        return [$validatedBranchData, $validatedOfficeData];
    }

    public function validateExistingUserData($request, $user_management)
    {
        $validStatus = ['active', 'inactive'];
        $validRemarks = ['Expired', 'Deactivated', 'On Hold', 'Locked', 'Expired', 'Resigned', 'Transferred', 'Others'];
        $validIsBranch = [1, 2];

        $alternateUserParams = [
            'position' => $request->position,
            'company' => $request->company,
            'isAlternateUser' => $request->isAlternateUser,
            'isBranchDealer' => $request->isBranchDealer,
            'branchDealerId' => $request->branchDealerId,
            'organization' => $request->organization,
        ];

        // First validate user data
        $validatedUserData = $request->validate([
            'fname' => ['required', $this->defaultRegexStructure],
            'mname' => ['nullable', $this->defaultRegexStructure],
            'lname' => ['required', $this->defaultRegexStructure],
            'email' => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($user_management->id)],
            'isAlternateUser' => $this->userManagementSyncPreparationService->checkAlternateUserValidity($alternateUserParams, $user_management->id),
            'position' => $this->userManagementSyncPreparationService->checkPositionValidity($request->isAlternateUser),
            'company' => 'required',
            'isBranchDealer' => ['required', Rule::in($validIsBranch)],
            'expiration_date' => ['nullable', 'date', 'after:today'],
            'status' => ['required', Rule::in($validStatus)],
            'remarks' => [
                function ($attribute, $value, $fail) use ($request, $validRemarks) {

                    $value = $value === '' ? null : $value;

                    if ($request->status === 'active' && !is_null($value)) {
                        $fail('Remarks must be empty when status is active.');
                    }

                    if ($request->status === 'inactive') {
                        if (is_null($value)) {
                            $fail(ucfirst($attribute) . ' is required when status is inactive.');
                        } elseif (!in_array($value, $validRemarks, true)) {
                            $fail('Invalid remark value.');
                        }
                    }
                }
            ],
            'role' => 'required'
        ], [
            'fname.regex' => 'The first name may only contain letters, spaces, and hyphens.',
            'mname.regex' => 'The middle name may only contain letters, spaces, and hyphens.',
            'lname.regex' => 'The last name may only contain letters, spaces, and hyphens.',
        ]);

        // Clear remarks if status is active
        if ($validatedUserData['status'] === 'active') {
            $validatedUserData['remarks'] = null;
        }

        // Validate branch/office based on isBranchDealer value
        $isBranchDealer = (int) $validatedUserData['isBranchDealer'];
        [$validatedBranchData, $validatedOfficeData] = $this->validateExistingUserBranchOrOffice($isBranchDealer, $request);

        // Format names
        $validatedUserData['fname'] = ucwords(strtolower($validatedUserData['fname']));
        $validatedUserData['mname'] = $validatedUserData['mname'] ? ucwords(strtolower($validatedUserData['mname'])) : null;
        $validatedUserData['lname'] = ucwords(strtolower($validatedUserData['lname']));

        return [$validatedUserData, $validatedBranchData, $validatedOfficeData];
    }

    public function clearUserAssociations($userId)
    {
        UserHasBranchDealer::where('user_id', $userId)->delete();
        UserHasHeadOffice::where('user_id', $userId)->delete();
    }

    public function syncBranchUser($user, $validatedBranchData)
    {
        if ($user->company === 'DEALER') {
            $branchDealerData = Dealer::select('id')->where('name', $validatedBranchData['branchDealerId'])->first();
        } else {
            $branchDealerData = Branch::select('id')->where('name', $validatedBranchData['branchDealerId'])->first();
        }

        $branchDealerData = [
            'user_id' => $user->id,
            'type' => ($user->company !== 'DEALER') ? 'BRANCH' : $user->company,
            'branch_id' => $branchDealerData->id
        ];

        UserHasBranchDealer::updateOrCreate(
            ['user_id' => $user->id],
            $branchDealerData,
        );
    }

    public function syncOfficeUser($user, $validatedOfficeData)
    {
        $organizationData = $this->userManagementSyncPreparationService->getHeadOfficeOrganizationName($validatedOfficeData['organization']);
        [$group_id, $division_id, $department_id, $section_id] = $this->userManagementSyncPreparationService->getHeadOfficeOrganizationId($organizationData);

        $officeData = [
            'user_id' => $user->id,
            'location' => $validatedOfficeData['location'],
            'group_id' => $group_id,
            'division_id' => $division_id,
            'department_id' => $department_id,
            'section_id' => $section_id,
        ];

        UserHasHeadOffice::updateOrCreate(
            ['user_id' => $user->id],
            $officeData
        );
    }

    public function syncExistingUserBranchOrOffice($user_management, $isBranchDealer, $oldIsBranch, $validatedBranchData, $validatedOfficeData)
    {
        $userId = $user_management->id;

        if ($isBranchDealer !== $oldIsBranch) {
            $this->clearUserAssociations($userId);
        }

        if ($isBranchDealer === 1) {
            $this->syncBranchUser($user_management, $validatedBranchData);
        } else {
            $this->syncOfficeUser($user_management, $validatedOfficeData);
        }
    }
}
