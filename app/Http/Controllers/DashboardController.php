<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function index()
    {
        return inertia("dashboard/index", ['data' => $this->dashboardService->prepareDashboardData()]);
    }
}
