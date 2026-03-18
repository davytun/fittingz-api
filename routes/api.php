<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\CronController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/cron/{secret}/queue', [CronController::class, 'processQueue'])
    ->where('secret', '[a-zA-Z0-9]+');

Route::prefix('v1/auth')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    });

    Route::middleware(['throttle:password-reset', 'throttle:password-reset-email'])->group(function () {
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('verification.verify');

    Route::middleware(['auth:sanctum', 'token.expiration'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
