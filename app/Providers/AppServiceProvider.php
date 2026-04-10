<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Payment;
use App\Policies\ClientPolicy;
use App\Policies\MeasurementPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // cPanel often runs older MySQL/MariaDB builds that cannot index utf8mb4
        // varchar(255) columns. Limiting the default string length keeps migrations portable.
        Schema::defaultStringLength(191);

        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Measurement::class, MeasurementPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-reset', function ($request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('password-reset-email', function ($request) {
            return Limit::perHour(5)->by($request->input('email'));
        });

        // API rate limiting per user
        RateLimiter::for('api', function ($request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });
    }
}
