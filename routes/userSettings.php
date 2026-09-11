<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Reports\UserReportsController;
use App\Http\Controllers\UserSettings\BranchController;
use App\Http\Controllers\UserSettings\DealerController;
use App\Http\Controllers\UserSettings\DepartmentController;
use App\Http\Controllers\UserSettings\DivisionController;
use App\Http\Controllers\UserSettings\GroupController;
use App\Http\Controllers\UserSettings\HeadOfficeController;
use App\Http\Controllers\UserSettings\RoleManagementController;
use App\Http\Controllers\UserSettings\SectionController;
use App\Http\Controllers\UserSettings\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check_is_reset_pass', 'check_password_validity', 'session_timeout'])->group(function () {

    // User Management-------------------------------------------------
    Route::resource('user-management', UserManagementController::class)
        ->only(["create"])
        ->middleware("permission:user_management.view");

    Route::resource('user-management', UserManagementController::class)
        ->only(["index"])
        ->middleware("permission:user_management.view");

    Route::resource('user-management', UserManagementController::class)
        ->only(["store"])
        ->middleware("permission:user_management.create");

    Route::get('user-management/view', function () {
        return redirect()->route('user-management.index');
    });

    Route::get('user-management/{user}' , [UserManagementController::class, 'show'])->name('user-management.show');
    Route::post('user-management/view', [UserManagementController::class, 'view'])->name('user-management.view');

    Route::resource('user-management', UserManagementController::class)
        ->only(["update"])
        ->except(['edit'])
        ->middleware("permission:user_management.edit");

    Route::resource('user-management', UserManagementController::class)
        ->only(["destroy"])
        ->middleware("permission:user_management.delete");

    Route::post('user-management/{user_management}/reset-password', [UserManagementController::class, 'resetPassword'])
        ->name('user-management.resetPassword')
        ->middleware("permission:user_management.reset");

    Route::post('user-management/export', [UserManagementController::class, 'export'])
        ->name('user-management.export')
        ->middleware("permission:user_management.export");
    // ----------------------------------------------------------------

    // Role Management-------------------------------------------------
    Route::resource('role-management', RoleManagementController::class)
        ->only(["create", "store"])
        ->middleware("permission:role_management.create");

    Route::resource('role-management', RoleManagementController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:role_management.edit");

    Route::get('role-management/{role}' , [RoleManagementController::class, 'show'])->name('role-management.show');
    Route::post('role-management/view', [RoleManagementController::class, 'view'])->name('role-management.view');

    Route::resource('role-management', RoleManagementController::class)
        ->only(["destroy"])
        ->middleware("permission:role_management.delete");

    Route::resource('role-management', RoleManagementController::class)
        ->only(["index", "show"])
        ->middleware("permission:role_management.view");
    // ----------------------------------------------------------------

    // Branch Management-----------------------------------------------
    Route::resource('branch-management', BranchController::class)
        ->only(["store"])
        ->except(["create"])
        ->middleware("permission:branch_management.create");

    Route::resource('branch-management', BranchController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:branch_management.edit");

    Route::resource('branch-management', BranchController::class)
        ->only(["destroy"])
        ->middleware("permission:branch_management.delete");

    Route::resource('branch-management', BranchController::class)
        ->only(["index"])
        ->except(["show"])
        ->middleware("permission:branch_management.view");
    // -----------------------------------------------------------------

    // Dealer Management-----------------------------------------------
    Route::resource('dealer-management', DealerController::class)
        ->only(["store"])
        ->except(["create"])
        ->middleware("permission:dealer_management.create");

    Route::resource('dealer-management', DealerController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:dealer_management.edit");

    Route::resource('dealer-management', DealerController::class)
        ->only(["destroy"])
        ->middleware("permission:dealer_management.delete");

    Route::resource('dealer-management', DealerController::class)
        ->only(["index"])
        ->except(["show"])
        ->middleware("permission:dealer_management.view");
    // -----------------------------------------------------------------

    // Group Management-------------------------------------------------
    Route::resource('group-management', GroupController::class)
        ->only(["store"])
        ->except(["create"])
        ->middleware("permission:group_management.create");

    Route::resource('group-management', GroupController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:group_management.edit");
    Route::resource('group-management', GroupController::class)
        ->only(["destroy"])
        ->middleware("permission:group_management.delete");

    Route::resource('group-management', GroupController::class)
        ->only(["index"])
        ->except(["show"])
        ->middleware("permission:group_management.view");
    // --------------------------------------------------------------------

    // Division Management-----------------------------------------------
    Route::resource('division-management', DivisionController::class)
        ->only(["store"])
        ->except(["create"])
        ->middleware("permission:division_management.create");

    Route::resource('division-management', DivisionController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:division_management.edit");

    Route::resource('division-management', DivisionController::class)
        ->only(["destroy"])
        ->middleware("permission:division_management.delete");

    Route::resource('division-management', DivisionController::class)
        ->only(["index"])
        ->except(["show"])
        ->middleware("permission:division_management.view");
    //-------------------------------------------------------------------
    // Department Management---------------------------------------------
    Route::resource('department-management', DepartmentController::class)
        ->only(["store"])
        ->except(["create"])
        ->middleware("permission:department_management.create");

    Route::resource('department-management', DepartmentController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:department_management.edit");

    Route::resource('department-management', DepartmentController::class)
        ->only(["destroy"])
        ->middleware("permission:department_management.delete");

    Route::resource('department-management', DepartmentController::class)
        ->only(["index"])
        ->except(["show"])
        ->middleware("permission:department_management.view");
    // -------------------------------------------------------------------

    // Section Management-------------------------------------------------
    Route::resource('section-management', SectionController::class)
        ->only(["store"])
        ->except(["create"])
        ->middleware("permission:section_management.create");

    Route::resource('section-management', SectionController::class)
        ->only(["update"])
        ->except(["edit"])
        ->middleware("permission:section_management.edit");

    Route::resource('section-management', SectionController::class)
        ->only(["destroy"])
        ->middleware("permission:section_management.delete");

    Route::resource('section-management', SectionController::class)
        ->only(["index"])
        ->except(["show"])
        ->middleware("permission:section_management.view");
    // --------------------------------------------

    // API Fetch Data for Head Office, and for Roles ----------------------
    Route::get('get-role-options', [HeadOfficeController::class, 'getRoleOptions'])->name('getRoleOptions');
    Route::get('get-branch-options', [HeadOfficeController::class, 'getBranchOptions'])->name('getBranchOptions');
    Route::get('get-dealer-options', [HeadOfficeController::class, 'getDealerOptions'])->name('getDealerOptions');
    Route::get('get-group-options', [HeadOfficeController::class, 'getGroupOptions'])->name('getGroupOptions');
    Route::get('get-division-options', [HeadOfficeController::class, 'getDivisionOptions'])->name('getDivisionOptions');
    Route::get('get-department-options', [HeadOfficeController::class, 'getDepartmentOptions'])->name('getDepartmentOptions');
    Route::get('get-section-options', [HeadOfficeController::class, 'getSectionOptions'])->name('getSectionOptions');
    Route::get('get-organization-options', [HeadOfficeController::class, 'getOrganizationOptions'])->name('getOrganizationOptions');
    Route::get('/get-area-options', [UserReportsController::class, 'getAreaOptions'])->name('get-area-options');
    Route::get('/get-module-options', [AuditLogController::class, 'getModuleOptions'])->name('getModuleOptions');
});
