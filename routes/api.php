<?php

declare(strict_types=1);

use Sparktech\Auth\Http\Controllers\CoreAuthController;
use Sparktech\Auth\Http\Controllers\OtpController;
use Sparktech\Auth\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('sparktech-auth.api_prefix', 'api'))
    ->middleware(config('sparktech-auth.middleware', ['api']))
    ->prefix(config('sparktech-auth.prefix', 'auth'))
    ->group(function (): void {
        Route::get('/health', static function () {
            return response()->json([
                'success' => true,
                'package' => 'sparktech/laravel-auth',
                'version' => '1.0.3',
                'status' => 'ready',
                'auth_driver' => 'sanctum',
            ]);
        });

        // Core Authentication
        Route::post('/register', [CoreAuthController::class, 'register']);
        Route::post('/login', [CoreAuthController::class, 'login']);
        Route::post('/email/verify-otp', [CoreAuthController::class, 'verifyEmailOtp']);
        Route::post('/email/resend-otp', [CoreAuthController::class, 'resendEmailOtp']);
        Route::post('/forgot-password', [CoreAuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [CoreAuthController::class, 'resetPassword']);

        // Social authentication
        Route::get('/social/{provider}/redirect', [SocialAuthController::class, 'redirect']);
        Route::get('/social/{provider}/callback', [SocialAuthController::class, 'callback']);

        // OTP foundation
        Route::post('/otp/send', [OtpController::class, 'send']);
        Route::post('/otp/verify', [OtpController::class, 'verify']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', [CoreAuthController::class, 'logout']);
            Route::post('/logout-all', [CoreAuthController::class, 'logoutAll']);
            Route::get('/me', [CoreAuthController::class, 'me']);
            Route::post('/change-password', [CoreAuthController::class, 'changePassword']);
            Route::post('/deactivate', [CoreAuthController::class, 'deactivate']);
        });
    });