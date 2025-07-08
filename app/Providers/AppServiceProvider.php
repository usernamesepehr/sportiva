<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)
                ->by(strtolower($request->input('email')) ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'درخواست‌های بیش از حد. لطفاً بعداً امتحان کنید'
                    ], 429);
                });
        });
        RateLimiter::for('apply', function (Request $request) {
            return Limit::perMinute(60)
                ->by(strtolower($request->input('email')) ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'درخواست‌های بیش از حد. لطفاً بعداً امتحان کنید'
                    ], 429);
                });
        });
        RateLimiter::for('cart', function (Request $request) {
            return Limit::perMinute(20)
                ->by(strtolower($request->input('email')) ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => false,
                        'message' => 'درخواست‌های بیش از حد. لطفاً بعداً امتحان کنید'
                    ], 429);
                });
        });
    }
}
