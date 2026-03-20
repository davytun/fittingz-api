<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ClientProfileController;
use App\Http\Controllers\Api\V1\MeasurementController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\StyleController;
use App\Http\Controllers\Api\V1\OrderStyleController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

Route::get('/cron/{secret}/queue', [CronController::class, 'processQueue'])
    ->where('secret', '[a-zA-Z0-9]+');

// AUTH ROUTES
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

// PROTECTED ROUTES
Route::prefix('v1')->middleware(['auth:sanctum', 'token.expiration'])->group(function () {

    // CLIENTS
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{client}/profile', [ClientProfileController::class, 'show']);

    // MEASUREMENTS (flat structure with query params)
    Route::post('measurements', [MeasurementController::class, 'store']);
    Route::get('measurements', [MeasurementController::class, 'index']);
    Route::get('measurements/{measurement}', [MeasurementController::class, 'show']);
    Route::patch('measurements/{measurement}', [MeasurementController::class, 'update']);
    Route::patch('measurements/{measurement}/set-default', [MeasurementController::class, 'setDefault']);
    Route::delete('measurements/{measurement}', [MeasurementController::class, 'destroy']);

    // ORDERS (flat structure with query params)
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}', [OrderController::class, 'update']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::patch('orders/{order}/measurement', [OrderController::class, 'updateMeasurement']);
    Route::delete('orders/{order}', [OrderController::class, 'destroy']);

    // PAYMENTS
    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('payments', [PaymentController::class, 'index']);
    Route::get('payments/{payment}', [PaymentController::class, 'show']);
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy']);
    
    // STYLES
    Route::post('styles', [StyleController::class, 'store']);
    Route::get('styles', [StyleController::class, 'index']);
    Route::get('styles/{style}', [StyleController::class, 'show']);
    Route::patch('styles/{style}', [StyleController::class, 'update']);
    Route::delete('styles/{style}', [StyleController::class, 'destroy']);

    // LINK STYLES TO ORDERS
    Route::get('orders/{order}/styles', [OrderStyleController::class, 'index']);
    Route::post('orders/{order}/styles', [OrderStyleController::class, 'attach']);
    Route::delete('orders/{order}/styles/{style}', [OrderStyleController::class, 'detach']);
    
    // DASHBOARD
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('recent-orders', [DashboardController::class, 'recentOrders']);
        Route::get('pending-payments', [DashboardController::class, 'pendingPayments']);
        Route::get('upcoming-deliveries', [DashboardController::class, 'upcomingDeliveries']);
        Route::get('overdue-orders', [DashboardController::class, 'overdueOrders']);
        Route::get('revenue-analytics', [DashboardController::class, 'revenueAnalytics']);
        Route::get('top-clients', [DashboardController::class, 'topClients']);
    });
});
