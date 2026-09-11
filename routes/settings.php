<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['prefix' => 'settings'], function () {
    Route::middleware(['auth', 'check_is_reset_pass', 'check_password_validity', 'session_timeout'])->group(function () {
        Route::redirect('/', 'settings/profile');

        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::post('/', [ProfileController::class, 'photoUpdate'])->name('profile.photo.update');
            Route::post('/remove', [ProfileController::class, 'photoRemove'])->name('profile.photo.remove');
            Route::patch('', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        Route::get('/appearance', function () {
            return Inertia::render('settings/appearance');
        })->name('appearance');
    });

    Route::middleware(['auth', 'session_timeout'])->group(function () {
        Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    });
});
