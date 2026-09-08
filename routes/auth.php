<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PhoneOtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    if (config('security.public_registration')) {
        Route::get('register', [RegisteredUserController::class, 'create'])
                    ->name('register');

        Route::post('register', [RegisteredUserController::class, 'store']);
    }

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('throttle:login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('login/telefono', [PhoneOtpController::class, 'show'])
                ->name('login.otp');

    Route::post('login/telefono', [PhoneOtpController::class, 'verify'])
                ->middleware('throttle:login')
                ->name('login.otp.validar');

    Route::post('login/telefono/reenviar', [PhoneOtpController::class, 'resend'])
                ->middleware('throttle:3,1')
                ->name('login.otp.reenviar');

    Route::get('two-factor/setup', [TwoFactorController::class, 'setup'])
                ->name('two-factor.setup');

    Route::get('two-factor/qr', [TwoFactorController::class, 'qr'])
                ->name('two-factor.qr');

    Route::get('two-factor/challenge', [TwoFactorController::class, 'challenge'])
                ->name('two-factor.challenge');

    Route::post('two-factor/confirm', [TwoFactorController::class, 'confirm'])
                ->middleware('throttle:login')
                ->name('two-factor.confirm');

    Route::post('two-factor/users/{id}/reset', [TwoFactorController::class, 'reset'])
                ->middleware(['rol:admin', '2fa'])
                ->name('two-factor.reset');

    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
