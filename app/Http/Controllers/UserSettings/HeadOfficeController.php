<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use App\Services\OfficesService;
use Illuminate\Http\Request;

class HeadOfficeController extends Controller
{
    protected $headOfficeService;

    public function __construct(OfficesService $headOfficeService)
    {
        $this->headOfficeService = $headOfficeService;
    }
    
    public function getBranchOptions()
    {
        return response()->json($this->headOfficeService->getBranches());
    }

    public function getDealerOptions()
    {
        return response()->json($this->headOfficeService->getDealers());
    }

    public function getGroupOptions()
    {
        return response()->json($this->headOfficeService->getGroups());
    }

    public function getDivisionOptions(Request $request)
    {
        return response()->json(
            $this->headOfficeService->getDivisions($request->group)
        );
    }

    public function getDepartmentOptions(Request $request)
    {
        return response()->json(
            $this->headOfficeService->getDepartments(
                $request->group,
                $request->division
            )
        );
    }

    public function getSectionOptions(Request $request)
    {
        return response()->json(
            $this->headOfficeService->getSections(
                $request->group,
                $request->division,
                $request->department
            )
        );
    }

    public function getOrganizationOptions()
    {
        return response()->json([
            'data' => $this->headOfficeService->getOrganizationOptions()
        ]);
    }

    // For Section Management Options
    public function getSectionManagementOptions()
    {
        return response()->json([
            'data' => $this->headOfficeService->getSectionManagementOptions()
        ]);
    }

    public function getRoleOptions()
    {
        return response()->json(
            $this->headOfficeService->getRoles()
        );
    }
}
