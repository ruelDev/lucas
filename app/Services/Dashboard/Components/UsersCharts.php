<?php

namespace App\Services\Dashboard\Components;

use App\Models\SessionLogs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersCharts

{
    public function prepareUserChartsData()
    {
        $user = Auth::user();

        // Initialize Query
        // do not remove it yet, for clarification of company access
        // $query = User::where('company', $user->company === 'BFC' ? 'BFC' : 'BMI');

        $query = User::query();

        [$totalCount, $newUsersLastMonthPercentage] = $this->getTotalUsers($query);

        [$newUsersToday, $todayYesterdayDiffPercentage] = $this->getNewUsers($query);

        [$activeUsers, $inactiveUsers] = $this->getUserStatusCounts($query);

        $sessionCounts = $this->getSessionCounts($user);

        return [
            'totalCount' => $totalCount,
            'newUsersLastMonthPercentage' => $newUsersLastMonthPercentage,
            'newUsersToday' => $newUsersToday,
            'todayYesterdayDiffPercentage' => $todayYesterdayDiffPercentage,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'sessionCounts' => $sessionCounts
        ];
    }

    private function getTotalUsers($query)
    {
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // get total count of users
        $totalCount = (clone $query)->count();

        $newUsersLastMonthCount = (clone $query)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $newUsersLastMonthPercentage = $totalCount > 0
            ? ($newUsersLastMonthCount / $totalCount) * 100
            : 0;

        return [
            $totalCount,
            $newUsersLastMonthPercentage
        ];
    }

    private function getNewUsers($query)
    {
        // get total count of new users created today
        $newUsersToday = (clone $query)
            ->whereDate('created_at', now())->count();

        $yesterday = Carbon::now()->subDay();
        $newUsersYesterday = (clone $query)
            ->whereDate('created_at', $yesterday)->count();

        if ($newUsersYesterday > 0) {
            $todayYesterdayDiffPercentage =
                (($newUsersToday - $newUsersYesterday) / $newUsersYesterday) * 100;
        } else {
            $todayYesterdayDiffPercentage = $newUsersToday > 0 ? 100 : 0;
        }

        return [
            $newUsersToday,
            $todayYesterdayDiffPercentage
        ];
    }

    private function getUserStatusCounts($query)
    {
        // get count of users based on status
        $activeUsers = (clone $query)
            ->where('status', 'active')->count();

        $inactiveUsers = (clone $query)
            ->where('status', 'inactive')->count();

        return [
            $activeUsers,
            $inactiveUsers
        ];
    }

    private function getSessionCounts()
    {
        // get count of user logins per day
        return SessionLogs::select(
            DB::raw('DATE(created_at) AS date'),
            DB::raw('COUNT(DISTINCT user_id) AS logins')
        )
            ->where('session_type', 'LOGIN')
            // ->whereHas('user', function ($q) use ($user) {
            //     $q->where('company', $user->company === 'BFC' ? 'BFC' : 'BMI');
            // })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'logins' => $item->logins
                ];
            });
    }
}
