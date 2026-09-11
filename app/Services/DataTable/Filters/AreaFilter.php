<?php

namespace App\Services\DataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AreaFilter
{
    private function applyBranchDealerFilter($query, $area)
    {
        return $query->whereHas('branchDealerUser', function ($q2) use ($area) {
            $q2->where(function ($q3) use ($area) {
                $q3->where('type', 'BRANCH')
                    ->whereHas('branch', fn($q4) => $q4->where('name', $area));
            })->orWhere(function ($q3) use ($area) {
                $q3->where('type', 'DEALER')
                    ->whereHas('dealer', fn($q4) => $q4->where('name', $area));
            });
        });
    }

    private function applyHeadOfficeFilter($query, $area)
    {
        // The picker's breadcrumb always ends with the leaf's plain name,
        // regardless of how many ancestor levels precede it (or whether
        // any were skipped) — so we only need the last segment.
        $leafName = trim(Str::afterLast($area, '->'));

        return $query->orWhereHas('headOfficeUser', function ($q2) use ($leafName) {
            $q2->where(function ($q3) use ($leafName) {
                $q3->whereHas('group', fn($q4) => $q4->where('name', $leafName))
                    ->orWhereHas('division', fn($q4) => $q4->where('name', $leafName))
                    ->orWhereHas('department', fn($q4) => $q4->where('name', $leafName))
                    ->orWhereHas('section', fn($q4) => $q4->where('name', $leafName));
            });
        });
    }

    public function apply(Builder $query, Request $request): void
    {
        if ($request->filled('area')) {
            $area = $request->area;

            $query->where(function ($q) use ($area) {
                $this->applyBranchDealerFilter($q, $area);
                $this->applyHeadOfficeFilter($q, $area);
            });
        }
    }
}
