<?php

namespace App\Services\UserSettings;

use App\Models\SessionLogs;
use App\Models\User;
use App\Services\DataTable\Filters\AreaFilter;
use App\Services\DataTable\Filters\DateFilter;
use App\Services\DataTable\Filters\RoleFilter;
use App\Services\UserSettings\UserManagementService;
use Illuminate\Http\Request;

class UserManagementExportService
{

    protected $userManagementService;
    protected DateFilter $dateFilter;
    protected RoleFilter $roleFilter;
    protected AreaFilter $areaFilter;

    public function __construct(UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
        $this->dateFilter = new DateFilter();
        $this->roleFilter = new RoleFilter();
        $this->areaFilter = new AreaFilter();
    }

    /**
     * Create a new class instance.
     */
    public function umeQuery(string $searchVal, string $sortBy, string $sortDir)
    {
        switch ($sortBy) {
            case 'name':
                $sort = 'fname';
                break;
            case 'area':
                $sort = 'position';
                break;
            case 'role':
                $sort = 'roles.name';
                break;
            case '':
                $sort = 'employee_id';
                break;
            default:
                $sort = $sortBy;
        }

        $userQuery = User::query()->with([
            'roles',
            'branchDealerUser.branch',
            'branchDealerUser.dealer',
            'headOfficeUser.group',
            'headOfficeUser.division',
            'headOfficeUser.department',
            'headOfficeUser.section',
        ])
            ->search($searchVal)
            ->orderBy($sort, $sortDir)
            ->get();

        return $userQuery->map(function ($user) {
            $user->name = $user->fname . " " . ($user->mname ?? '') . " " . $user->lname;

            $area = $this->prepareOfficeType($user);

            return [
                'employee_id' => $user->employee_id,
                'fname' => $user->fname,
                'mname' => $user->mname ?? '',
                'lname' => $user->lname,
                'email' => $user->email,
                'position' => $user->position,
                'area' => $area,
                'company' => $user->company,
                'role' => $user->roles->first()?->name,
                'status' => $user->status,
                'remarks' => $user->remarks,
                'updated_at' => $user->updated_at,
            ];
        });
    }

    protected function prepareOfficeType($user)
    {
        if ($user->isBranchDealer == 1) {
            $branchDealerUser = $user->branchDealerUser;

            if ($branchDealerUser->type === 'BRANCH') {
                $area = $branchDealerUser->branch ? $branchDealerUser->branch->name : 'N/A';
            } elseif ($branchDealerUser->type === 'DEALER') {
                $area = $branchDealerUser->dealer ? $branchDealerUser->dealer->name : 'N/A';
            }
        } else {
            $headOfficeUser = $user->headOfficeUser;
            $area =
                ($headOfficeUser->section->name ?? "") . " " .
                ($headOfficeUser->department->name ?? "") . " " .
                ($headOfficeUser->division->name ?? "") . " " .
                ($headOfficeUser->group->name ?? "") . " " . $headOfficeUser->location;
        }

        return $area;
    }

    public function getLastSession(int $userId)
    {
        $last_login = SessionLogs::where('user_id', $userId)
            ->where('session_type', 'LOGIN')
            ->latest()
            ->first();

        $last_logout = SessionLogs::where('user_id', $userId)
            ->where('session_type', 'LOGOUT')
            ->latest()
            ->first();

        return [
            'last_login' => $last_login?->created_at,
            'last_logout' => $last_logout?->created_at,
        ];
    }

    /**
     * Get Data for User Report Generation
     */
    public function getUsersForUserReport(Request $request)
    {
        $query = User::query();

        $query->with([
            'roles',
            'branchDealerUser.branch',
            'branchDealerUser.dealer',
            'headOfficeUser.group',
            'headOfficeUser.division',
            'headOfficeUser.department',
            'headOfficeUser.section'
        ]);

        $this->dateFilter->apply($query, $request);
        $this->roleFilter->apply($query, $request);
        $this->areaFilter->apply($query, $request);

        return $query->get()->map(function ($item) {
            $formatDate = fn($date) => $date?->format('Y-m-d H:i:s');

            $cleanSpaceText = fn($text) => $text === '' ? null : $text;

            $name = $item->fname . " " . ($item->mname ?? '') . " " . $item->lname;

            $areaRaw = $this->userManagementService
                ->prepareHeadOfficeUserOrBranchDealerUser($item);

            $area = trim(preg_replace(
                '/\s+/',
                ' ',
                strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $areaRaw))
            ));

            $role = $item->roles->first()?->name;

            $session = $this->getLastSession($item->id);

            return [
                'employee_id' => $item->employee_id,
                'name' => $name,
                'role' => $role,
                'area' => $area,
                'date_created' => $formatDate($item->created_at),
                'last_login_date' => $formatDate($session['last_login']) ?? 'N/A',
                'last_logout_date' => $formatDate($session['last_logout']) ?? 'N/A',
                'last_password_change' => $item->password_changed_at  ?? 'N/A',
                'account_status' => $item->status  ?? 'N/A',
                'remarks' => $cleanSpaceText($item->remarks) ?? 'N/A',
                'account_expiration_date' => $item->expiration_date  ?? 'N/A',
            ];
        })->toArray();
    }
}
