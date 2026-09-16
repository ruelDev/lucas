<?php

namespace App\Services\Dashboard;

use App\Services\Dashboard\Components\CertificateOfFullPaymentCharts;
use App\Services\Dashboard\Components\MasterSetupCharts;
use App\Services\Dashboard\Components\ReceiptConverterCharts;
use App\Services\Dashboard\Components\RolesCharts;
use App\Services\Dashboard\Components\UsersCharts;
use Illuminate\Support\Facades\Auth;

class DashboardService
{

    protected UsersCharts $userCharts;
    protected RolesCharts $roleCharts;
    protected MasterSetupCharts $masterSetupCharts;
    protected CertificateOfFullPaymentCharts $certificateOfFullPaymentCharts;
    protected ReceiptConverterCharts $receiptConverterCharts;

    public function __construct()
    {
        $this->userCharts = new UsersCharts();
        $this->roleCharts = new RolesCharts();
        $this->masterSetupCharts = new MasterSetupCharts();
        $this->certificateOfFullPaymentCharts = new CertificateOfFullPaymentCharts();
        $this->receiptConverterCharts = new ReceiptConverterCharts();
    }

    public function prepareDashboardData()
    {
        $currentUser = Auth::user();

        $userData = $this->userCharts->prepareUserChartsData();
        $roleData = $this->roleCharts->prepareRoleChartsData();
        $masterSetupData = $this->masterSetupCharts->prepareMasterSetupChartsData();
        // Temporarily disabled — queries remote sqlsrv_bmilmsdb/sqlsrv_bfclmsdb connections
        // that aren't reachable right now. Re-enable once those DBs are needed again.
        // $certificateOfFullPaymentData = $this->certificateOfFullPaymentCharts->prepareCertificateOfFullPaymentChartsData();
        $certificateOfFullPaymentData = null;
        $receiptConverterData = $this->receiptConverterCharts->prepareReceiptConverterChartsData();


        return [
            "users" => $currentUser->can("user_management.view") ? $userData : null,
            "roles" => $currentUser->can("role_management.view") ? $roleData : null,
            "masterSetup" => $currentUser->canAny(["branch_management.view", "dealer_management.view", "group_management.view", "division_management.view", "department_management.view", "section_management.view"]) ? $masterSetupData : null,
            "certificateOfFullPayment" => $currentUser->can("certificate_fullpayment.view") ? $certificateOfFullPaymentData : null,
            "receiptConverter" => $currentUser->can("receipt_converter.view") ? $receiptConverterData : null,
        ];
    }
}
