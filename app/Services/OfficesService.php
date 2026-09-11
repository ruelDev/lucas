<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Support\Collection;

class OfficesService
{
    public function getBranches(): Collection
    {
        return Branch::get()->map(fn($item) => [
            'name' => $item->name,
        ]);
    }

    public function getDealers(): Collection
    {
        return Dealer::get()->map(fn($item) => [
            'name' => $item->name,
        ]);
    }

    public function getGroups(): Collection
    {
        return Group::get()->map(fn($item) => [
            'name' => $item->name,
        ]);
    }

    public function getDivisions($groupName): Collection
    {
        $group = Group::select('id')->where('name', $groupName)->first();

        if (!$group) {
            return collect();
        }

        return Division::where('group_id', $group->id)->get()
            ->map(fn($item) => ['name' => $item->name]);
    }

    public function getDepartments($groupName, $divisionName): Collection
    {
        $query = Department::query();

        if ($groupName) {
            $group = Group::select('id')->where('name', $groupName)->first();
            if ($group) {
                $query->where('group_id', $group->id);
            }
        }

        if ($divisionName) {
            $division = Division::select('id')->where('name', $divisionName)->first();
            if ($division) {
                $query->where(
                    fn($q) =>
                    $q->where('division_id', $division->id)
                        ->orWhereNull('division_id')
                );
            }
        } else {
            $query->whereNull('division_id');
        }

        return $query->get()->map(fn($item) => [
            'name' => $item->name,
        ]);
    }

    public function getSections($groupName, $divisionName, $departmentName): Collection
    {
        $query = Section::query();

        if ($groupName) {
            $group = Group::select('id')->where('name', $groupName)->first();
            if ($group) {
                $query->where('group_id', $group->id);
            }
        }

        if ($divisionName) {
            $division = Division::select('id')->where('name', $divisionName)->first();
            if ($division) {
                $query->where(
                    fn($q) =>
                    $q->where('division_id', $division->id)
                        ->orWhereNull('division_id')
                );
            }
        } else {
            $query->whereNull('division_id');
        }

        if ($departmentName) {
            $department = Department::select('id')->where('name', $departmentName)->first();
            if ($department) {
                $query->where(
                    fn($q) =>
                    $q->where('department_id', $department->id)
                        ->orWhereNull('department_id')
                );
            }
        } else {
            $query->whereNull('department_id');
        }

        return $query->get()->map(fn($item) => [
            'name' => $item->name,
        ]);
    }

    public function getOrganizationOptions(): Collection
    {
        return collect()
            ->merge(
                Group::get()->map(fn($item) => [
                    'name' => $item->name,
                    'code' => $item->code,
                    'type' => 'GROUP',
                ])
            )
            ->merge(
                Division::with('group')->get()->map(fn($item) => [
                    'name' => $this->buildName([$item->group], $item),
                    'code' => $item->code,
                    'type' => 'DIVISION',
                ])
            )
            ->merge(
                Department::with(['group', 'division'])->get()->map(fn($item) => [
                    'name' => $this->buildName([$item->group, $item->division], $item),
                    'code' => $item->code,
                    'type' => 'DEPARTMENT',
                ])
            )
            ->merge(
                Section::with(['group', 'division', 'department'])->get()->map(fn($item) => [
                    'name' => $this->buildName([$item->group, $item->division, $item->department], $item),
                    'code' => $item->code,
                    'type' => 'SECTION',
                ])
            );
    }

    public function getSectionManagementOptions(): Collection
    {
        return collect()
            ->merge(
                Group::get()->map(fn($item) => [
                    'name' => $item->name,
                    'code' => $item->code,
                    'type' => 'GROUP',
                ])
            )
            ->merge(
                Division::with('group')->get()->map(fn($item) => [
                    'name' => $this->buildName([$item->group], $item),
                    'code' => $item->code,
                    'type' => 'DIVISION',
                ])
            )
            ->merge(
                Department::with(['group', 'division'])->get()->map(fn($item) => [
                    'name' => $this->buildName([$item->group, $item->division], $item),
                    'code' => $item->code,
                    'type' => 'DEPARTMENT',
                ])
            );
    }

    public function getRoles(): Collection
    {
        return Role::select('name')
            ->whereNotIn('name', ['Admin', 'SuperAdmin'])
            ->get()
            ->map(fn($item) => ['name' => $item->name]);
    }

    private function buildName(array $hierarchy, $item): string
    {
        $code = collect($hierarchy)
            ->filter()
            ->pluck('code')
            ->implode('->');

        return ($code ? $code . '->' : '') . $item->name;
    }

    public function getAllAreaOptions(): Collection
    {
        return collect()
            ->merge($this->getBranches()->map(fn($item) => [
                ...$item,
                'type' => 'BRANCH',
            ]))
            ->merge($this->getDealers()->map(fn($item) => [
                ...$item,
                'type' => 'DEALER',
            ]))
            ->merge($this->getOrganizationOptions())
            ->values();
    }

    public function getModuleOptions() : Collection
    {
        return AuditLog::select('module')
            ->distinct()
            ->get()
            ->map(fn ($item) => ['name' => $item->module]);
    }
}
