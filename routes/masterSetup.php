<?php

use App\Http\Controllers\MasterSetup\BankManagement\BankAccountManagementController;
use App\Http\Controllers\MasterSetup\BankManagement\BankManagementController;
use App\Http\Controllers\MasterSetup\CertificateofFullpaymentController;
use App\Http\Controllers\MasterSetup\OfflineSearchFacilityController;
use App\Http\Controllers\MasterSetup\OutCollection\SearchController;
use App\Http\Controllers\MasterSetup\OutCollection\AuthorizerController;
use App\Http\Controllers\MasterSetup\OutCollection\DepositController;
use App\Http\Controllers\MasterSetup\OutCollection\PaymentController;
use Illuminate\Support\Facades\Route;

define('OC_CREATE', 'permission:out_collection.create');
define('OC_EDIT',   'permission:out_collection.edit');
define('OC_DELETE', 'permission:out_collection.delete');
define('OC_VIEW',   'permission:out_collection.view');
define('OC_AUTH',   'permission:out_collection.authorize');

define('OSF_VIEW', 'permission:offline_search_facility.view');

Route::middleware(['auth', 'check_is_reset_pass', 'check_password_validity', 'session_timeout'])->group(function () {

    // ======================== Certificate of Full Payment ========================
    Route::group(['prefix' => 'certificate-of-full-payment'], function () {
        Route::get('/get-cfp-signatory', [CertificateofFullpaymentController::class, 'getCfpSignatory'])->name('cfp.getCfpSignatory');
        Route::get('/', [CertificateofFullpaymentController::class, 'index'])->name('cfp.index');
        Route::get('/{cfp}/generate', [CertificateofFullpaymentController::class, 'show'])
            ->name('cfp.show')
            ->middleware("permission:certificate_fullpayment.view");
        Route::post('reports/generate/jasper', [CertificateofFullpaymentController::class, 'generateJasperPdf'])
            ->name('cfp.generateJasperPdfCfp')
            ->middleware("permission:certificate_fullpayment.export");
        Route::get('reports/output/view', [CertificateofFullpaymentController::class, 'streamPdf'])->name('cfp.viewCfp');
    });

    // ======================== Offline Search Facility ========================
    Route::middleware(OSF_VIEW)->prefix('offline-search-facility')->name('offline-search-facility.')->group(function () {
        Route::get('/', [OfflineSearchFacilityController::class, 'index'])->name('index');
        Route::get('/search', [OfflineSearchFacilityController::class, 'searchCustomer'])->name('searchCustomer');
        Route::get('/view', [OfflineSearchFacilityController::class, 'viewCustomer'])->name('viewCustomer');
        Route::get('/customer-data', [OfflineSearchFacilityController::class, 'customerData'])->name('customerData');
        Route::get('/customer-los-data', [OfflineSearchFacilityController::class, 'customerLosData'])->name('customerLosData');
        Route::get('/customer-lms-data', [OfflineSearchFacilityController::class, 'customerLmsData'])->name('customerLmsData');
    });

    // ======================== Out Collection ========================
    Route::middleware(OC_VIEW)->prefix('out-collection')->name('out-collection.')->group(function () {

        //Deposit
        Route::get('/', [DepositController::class, 'index'])->name('index');
        Route::get('/add-new-deposit', [DepositController::class, 'create'])->name('create')->middleware(OC_CREATE);
        Route::post('/store-new-deposit', [DepositController::class, 'store'])->name('store')->middleware(OC_CREATE);
        Route::get('/{deposit}/edit-deposit', [DepositController::class, 'edit'])->name('edit')->middleware(OC_EDIT);
        Route::put('/{deposit}/update-deposit', [DepositController::class, 'update'])->name('update')->middleware(OC_EDIT);
        Route::delete('/{deposit}/destroy-deposit', [DepositController::class, 'destroy'])->name('destroy')->middleware(OC_DELETE);

        // Payments
        Route::prefix('{deposit}/view-deposit')->name('view-deposit.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');

            // Quick Receipt
            Route::prefix('quick-receipt')->name('quick-receipt.')->group(function () {
                Route::get('/', [PaymentController::class, 'createQuickReceipt'])->name('create');
                Route::post('/store-quick-receipt', [PaymentController::class, 'storeQuickReceipt'])->name('store')->middleware(OC_CREATE);
            });

            // Unapplied Receipt
            Route::prefix('unapplied-receipt')->name('unapplied-receipt.')->group(function () {
                Route::get('/', [PaymentController::class, 'createUnappliedReceipt'])->name('create')->middleware(OC_CREATE);
                Route::post('/store-unapplied-receipt', [PaymentController::class, 'storeUnappliedReceipt'])->name('store')->middleware(OC_CREATE);
            });

            // Edit Receipt
            Route::get('{payment}/edit-receipt', [PaymentController::class, 'editReceipt'])->name('edit-receipt')->middleware(OC_EDIT);
            Route::put('{payment}/update-receipt', [PaymentController::class, 'updateReceipt'])->name('update-payment')->middleware(OC_EDIT);

            // Delete Receipt
            Route::delete('/{payment}/destroy-payment', [PaymentController::class, 'destroy'])->name('destroy-payment')->middleware(OC_DELETE);

            Route::get('/file', [PaymentController::class, 'viewFile'])->name('file');
        });

        // Authorize Payment
        Route::prefix('authorization')->name('authorization.')->group(function () {
            Route::post('/sent-to-author', [AuthorizerController::class, 'sentToAuthor'])->name('sent-to-author');
            Route::post('/authorize', [AuthorizerController::class, 'authorize'])->name('authorize')->middleware(OC_AUTH);
            Route::post('/send-back', [AuthorizerController::class, 'sendBack'])->name('send-back')->middleware(OC_AUTH);
        });

        // Search
        Route::prefix('search')->name('search.')->group(function () {
            Route::post('/', [SearchController::class, 'index'])->name('index');
        });
    });

    // ======================== Bank Management Module ========================
    Route::middleware('permission:bank_management.view')->prefix('bank-management')->name('bank-management.')->group(function () {
        Route::get('/', [BankManagementController::class, 'index'])->name('index');
        Route::get('/add-new-bank', [BankManagementController::class, 'create'])->name('create');
        Route::post('/store-bank', [BankManagementController::class, 'store'])->name('store');
        Route::get('/{bank}/edit-bank', [BankManagementController::class, 'edit'])->name('edit');
        Route::put('/{bank}/update-bank', [BankManagementController::class, 'update'])->name('update');
        Route::delete('/{bank}/destroy-bank', [BankManagementController::class, 'destroy'])->name('destroy');

        // Bank Account Management
        Route::prefix('{bank}/bank-accounts')->name('bank-accounts.')->group(function () {
            Route::get('/', [BankAccountManagementController::class, 'index'])->name('index');
            Route::get('/add-new-bank-account', [BankAccountManagementController::class, 'create'])->name('create');
            Route::post('/store-bank-account', [BankAccountManagementController::class, 'store'])->name('store');
            Route::get('/{bankAccount}/edit-bank-account', [BankAccountManagementController::class, 'edit'])->name('edit');
            Route::put('/{bankAccount}/update-bank-accounts', [BankAccountManagementController::class, 'update'])->name('update');
            Route::delete('/{bankAccount}/destroy-bank-account', [BankAccountManagementController::class, 'destroy'])->name('destroy');
        });
    });
});
