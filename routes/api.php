<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ClientMeasurementController;
use App\Http\Controllers\Api\V1\ClientOrderController;
use App\Http\Controllers\Api\V1\ClientOrderStyleController;
use App\Http\Controllers\Api\V1\ClientProfileController;
use App\Http\Controllers\Api\V1\OrderPaymentController;
use App\Http\Controllers\Api\V1\StyleController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', [App\Http\Controllers\HealthCheckController::class, 'check']);

// Cron
Route::get('/cron/{secret}/queue', [CronController::class, 'processQueue'])
    ->where('secret', '[a-zA-Z0-9]+');

Route::get('/cron/{secret}/run/{command}', [CronController::class, 'runCommand'])
    ->where('secret', '[a-zA-Z0-9]+')
    ->where('command', 'storage-link|optimize|cache-clear|route-clear|config-clear');

// API
Route::prefix('v1')->group(function () {
    
    // AUTH ROUTES
    Route::prefix('auth')->group(function () {
        Route::middleware('throttle:auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
            Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
        });

        Route::middleware(['throttle:password-reset', 'throttle:password-reset-email'])->group(function () {
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });


        Route::middleware(['auth:sanctum', 'token.expiration'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    // PROTECTED ROUTES
    Route::middleware(['auth:sanctum', 'token.expiration', 'throttle:api', 'log.api'])->group(function () {
        
        // PROFILE
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

        // CLIENTS
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::patch('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::scopeBindings()
            ->prefix('clients/{client}')
            ->name('clients.')
            ->group(function () {
                Route::get('profile', [ClientProfileController::class, 'show'])->name('profile');

                Route::get('measurements', [ClientMeasurementController::class, 'index'])->name('measurements.index');
                Route::post('measurements', [ClientMeasurementController::class, 'store'])->name('measurements.store');
                Route::get('measurements/{measurement}', [ClientMeasurementController::class, 'show'])->name('measurements.show');
                Route::patch('measurements/{measurement}', [ClientMeasurementController::class, 'update'])->name('measurements.update');
                Route::delete('measurements/{measurement}', [ClientMeasurementController::class, 'destroy'])->name('measurements.destroy');
                Route::patch('measurements/{measurement}/set-default', [ClientMeasurementController::class, 'setDefault'])
                    ->name('measurements.set-default');

                Route::prefix('orders')->name('orders.')->group(function () {
                    Route::get('/', [ClientOrderController::class, 'index'])->name('index');
                    Route::post('/', [ClientOrderController::class, 'store'])->name('store');

                    Route::prefix('{order}')->group(function () {
                        Route::get('/', [ClientOrderController::class, 'show'])->name('show');
                        Route::patch('/', [ClientOrderController::class, 'update'])->name('update');
                        Route::delete('/', [ClientOrderController::class, 'destroy'])->name('destroy');
                        Route::patch('status', [ClientOrderController::class, 'updateStatus'])->name('status.update');
                        Route::patch('measurement', [ClientOrderController::class, 'updateMeasurement'])->name('measurement.update');

                        Route::prefix('payments')->name('payments.')->group(function () {
                            Route::get('/', [OrderPaymentController::class, 'index'])->name('index');
                            Route::post('/', [OrderPaymentController::class, 'store'])->name('store');
                            Route::get('{payment}', [OrderPaymentController::class, 'show'])->name('show');
                            Route::delete('{payment}', [OrderPaymentController::class, 'destroy'])->name('destroy');
                        });

                        Route::prefix('styles')->name('styles.')->group(function () {
                            Route::post('/', [ClientOrderStyleController::class, 'attach'])->name('store');
                            Route::delete('{style}', [ClientOrderStyleController::class, 'detach'])->name('destroy');
                        });
                    });
                });
            });

        // STYLES
        Route::post('styles', [StyleController::class, 'store']);
        Route::get('styles', [StyleController::class, 'index']);
        Route::get('styles/{style}', [StyleController::class, 'show']);
        Route::patch('styles/{style}', [StyleController::class, 'update']);
        Route::delete('styles/{style}', [StyleController::class, 'destroy']);

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
});
