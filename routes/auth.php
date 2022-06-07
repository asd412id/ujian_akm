<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'guest:peserta'])->group(function () {
    $configs = [];
    $configs['allow_register'] = false;
    if (Storage::exists('configs.json')) {
        $configs = file_get_contents(Storage::path('configs.json'));
        if (isValidJSON($configs)) {
            $configs = json_decode($configs, true);
        } else {
            $configs = [];
            $configs['allow_register'] = false;
        }
    }

    if ($configs['allow_register']) {
        Route::get('daftar', [RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('daftar', [RegisteredUserController::class, 'store']);
    }

    Route::get('admin', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('admin', [AuthenticatedSessionController::class, 'store']);

    Route::get('lupa-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('lupa-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verifikasi-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');

    Route::get('verifikasi-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/notif-verifikasi', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('konfirmasi-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('konfirmasi-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('keluar', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
