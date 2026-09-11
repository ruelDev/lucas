<?php

namespace App\Http\Controllers\MasterSetup;

use App\Http\Controllers\Controller;
use App\Services\OfflineSearchFacility\OffineSearchFacilityService;
use Illuminate\Http\Request;

class OfflineSearchFacilityController extends Controller
{
    protected $service;

    public function __construct(
        OffineSearchFacilityService $offlineSearchFacilityService
    ) {
        $this->service = $offlineSearchFacilityService;
    }

    public function index(Request $request)
    {
        return inertia('main-modules/osf/index');
    }

    public function searchCustomer(Request $request)
    {
        return $this->service->searchCustomerData($request->all());
    }

    public function viewCustomer(Request $request)
    {
        return inertia('main-modules/osf/customer-loan-data/index', $request->all());
    }

    public function customerData(Request $request)
    {
        return $this->service->searchCustomerDetailsData($request->customerID);
    }

    public function customerLosData(Request $request)
    {
        return $this->service->searchCustomerLosData($request->applicationId);
    }

    public function customerLmsData(Request $request)
    {
        return $this->service->searchCustomerLmsData($request->customerID);
    }
}
