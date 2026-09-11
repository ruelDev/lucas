<?php

use App\Http\Controllers\Reports\ReceiptConverterController;
use App\Http\Controllers\Reports\OutCollection\AuthorizeController;
use App\Http\Controllers\Reports\OutCollection\DailyCollectionController;
use App\Http\Controllers\Reports\OutCollection\UnauthorizedController;
use App\Http\Controllers\Reports\LmsPosting\FinnoneUaController;
use App\Http\Controllers\Reports\LmsPosting\NewgenQrUaController;
use App\Http\Controllers\Reports\LmsPosting\FinnoneQrController;
use App\Http\Controllers\Reports\UserReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check_is_reset_pass', 'check_password_validity', 'session_timeout'])->group(function () {

    // ======================== Receipt Converter ========================
    Route::group(['prefix' => 'receipt-converter'], function () {
        Route::get('', [ReceiptConverterController::class, 'converter'])
            ->name('rpConverter')
            ->middleware("permission:receipt_converter.view");
        Route::post('/bulkUpload', [ReceiptConverterController::class, 'bulkUpload'])
            ->name('rpBulkUpload')
            ->middleware("permission:receipt_converter.validate");
        Route::post('/bulkConvert', [ReceiptConverterController::class, 'bulkConvert'])
            ->name('bulkConvert')
            ->middleware("permission:receipt_converter.export");
    });

    // ======================== Out Collection Reports ========================
    Route::prefix('out-collection-report')->name('out-collection-report.')->group(function () {

        // Daily Collection Report
        Route::middleware("permission:daily_collection_report.view")->prefix('daily-collection')->name('daily-collection.')->group(function () {
            Route::get('/', [DailyCollectionController::class, 'index'])->name('index');
            Route::get('export', [DailyCollectionController::class, 'export'])->name('export');
        });

        // Authorized Report
        Route::middleware("permission:out_collection_report.view")->prefix('authorized')->name('authorized.')->group(function () {
            Route::get('/', [AuthorizeController::class, 'index'])->name('index');
            Route::get('export', [AuthorizeController::class, 'export'])->name('export');
        });

        // Unauthorized Report
        Route::middleware("permission:out_collection_report.view")->prefix('unauthorized')->name('unauthorized.')->group(function () {
            Route::get('/', [UnauthorizedController::class, 'index'])->name('index');
            Route::get('export', [UnauthorizedController::class, 'export'])->name('export');
        });

        // LMS Posting Report
        Route::middleware("permission:lms_posting_report.view")->prefix('lms-posting')->name('lms-posting.')->group(function () {

            // Daily Collection Report
            Route::prefix('finnone-ua')->name('finnone-ua.')->group(function () {
                Route::get('/', [FinnoneUaController::class, 'index'])->name('index');
                Route::get('/export-finnone-ua', [FinnoneUaController::class, 'export'])->name('export');
            });

            // Authorized Report
            Route::prefix('finnone-qr')->name('finnone-qr.')->group(function () {
                Route::get('/', [FinnoneQrController::class, 'index'])->name('index');
                Route::get('/export-finnone-qr', [FinnoneQrController::class, 'export'])->name('export');
            });

            // Unauthorized Report
            Route::prefix('newgen-qr-ua')->name('newgen-qr-ua.')->group(function () {
                Route::get('/', [NewgenQrUaController::class, 'index'])->name('index');
                Route::get('/export-newgen-qr-ua', [NewgenQrUaController::class, 'export'])->name('export');
            });
        });
    });


    // ======================== Users Report ========================
    Route::middleware("permission:user_management.export")->prefix('user-reports')->name('user-reports.')->group(function () {
        Route::get('/', [UserReportsController::class, 'index'])->name('index');
        Route::get('/generate-report', [UserReportsController::class, 'generateJasperReport'])->name('generate');
    });
});
