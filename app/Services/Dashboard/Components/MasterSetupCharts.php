<?php

namespace App\Services\Dashboard\Components;

use App\Models\Branch;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Section;
use App\Models\User;

class MasterSetupCharts
{
    public function prepareMasterSetupChartsData()
    {
        [
            $totalBranches,
            $totalDealers,
            $totalGroups,
            $totalDivisions,
            $totalDepartments,
            $totalSections
        ] = $this->getTotalCounts();

        [
            $headOfficeUsers,
            $branchUsers,
            $dealerUsers
        ] = $this->getUserCountPerOrganization();

        return [
            'totalBranches' => $totalBranches,
            'totalDealers' => $totalDealers,
            'totalGroups' => $totalGroups,
            'totalDivisions' => $totalDivisions,
            'totalDepartments' => $totalDepartments,
            'totalSections' => $totalSections,
            'headOfficeUsers' => $headOfficeUsers,
            'branchUsers' => $branchUsers,
            'dealerUsers' => $dealerUsers
        ];
    }

    private function getTotalCounts()
    {
        $totalBranches = Branch::count();
        $totalDealers = Dealer::count();
        $totalGroups = Group::count();
        $totalDivisions = Division::count();
        $totalDepartments = Department::count();
        $totalSections = Section::count();

        return [
            $totalBranches,
            $totalDealers,
            $totalGroups,
            $totalDivisions,
            $totalDepartments,
            $totalSections
        ];
    }

    private function getUserCountPerOrganization()
    {
        // do not remove it yet, for clarification of company access
        // $query = User::with(['branchDealerUser'])->where('company', $user->company === 'BFC' ? 'BFC' : 'BMI');
        $query = User::with(['branchDealerUser']);

        $headOfficeUsers = (clone $query)
            ->where('isBranchDealer', 2)->count();

        $branchUsers = (clone $query)
            ->where('isBranchDealer', 1)
            ->whereHas('branchDealerUser', function ($q) {
                $q->where('type', 'BRANCH');
            })->count();

        $dealerUsers = (clone $query)
            ->where('isBranchDealer', 1)
            ->whereHas('branchDealerUser', function ($q) {
                $q->where('type', 'DEALER');
            })->count();

        return [
            $headOfficeUsers,
            $branchUsers,
            $dealerUsers
        ];
    }
}
