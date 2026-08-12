<?php

declare(strict_types=1);

namespace Sparktech\Auth\Http\Controllers;

use Sparktech\Auth\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Sparktech\Auth\Services\OtpService;

final class CoreAuthController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(config('sparktech-auth.password.min_length', 8))],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $model = config('sparktech-auth.user_model');
        $user = new $model();
        $user->name = $request->string('name')->toString();
        $user->email = $request->string('email')->toString();
        $user->password = Hash::make($request->string('password')->toString());
        $user->save();

        app(OtpService::class)->sendEmail($user->email, 'verification');

        return ApiResponse::success('Registration successful. Verification OTP sent to your email.', [
            'user' => $user->fresh(),
            'email_verification_required' => true,
            'otp_expires_in' => (int) config('sparktech-auth.otp.expires_in', 5) * 60,
        ], 201);
    }

    public function verifyEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $model = config('sparktech-auth.user_model');
        $user = $model::query()->where('email', $request->string('email')->toString())->first();

        if (! $user) {
            return ApiResponse::error('User not found.', null, 404);
        }

        if (! app(\Sparktech\Auth\Services\OtpService::class)->verify(
            $user->email,
            $request->string('otp')->toString(),
            'verification'
        )) {
            return ApiResponse::error('Invalid or expired OTP.', null, 422);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $token = $user->createToken('web')->plainTextToken;

        return ApiResponse::success('Email verified successfully.', [
            'user' => $user->fresh(),
            'email_verified' => true,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function resendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $model = config('sparktech-auth.user_model');
        $user = $model::query()->where('email', $request->string('email')->toString())->first();

        if (! $user) {
            return ApiResponse::error('User not found.', null, 404);
        }

        app(\Sparktech\Auth\Services\OtpService::class)->sendEmail($user->email, 'verification');

        return ApiResponse::success('Verification OTP resent successfully.');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $model = config('sparktech-auth.user_model');
        $user = $model::query()->where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), (string) $user->password)) {
            return ApiResponse::error('Invalid email or password.', null, 401);
        }

        $token = $user->createToken(
            $request->string('device_name', 'web')->toString()
        )->plainTextToken;

        return ApiResponse::success('Login successful.', [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'remember' => (bool) $request->boolean('remember'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success('Logout successful.');
    }

    public function logoutAll(Request $request)
    {
        $request->user()?->tokens()->delete();

        return ApiResponse::success('All devices logged out.');
    }

    public function me(Request $request)
    {
        return ApiResponse::success('Authenticated user.', ['user' => $request->user()]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordRule::min(config('sparktech-auth.password.min_length', 8))],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $user = $request->user();

        if (! Hash::check($request->string('current_password')->toString(), (string) $user->password)) {
            return ApiResponse::error('Current password is incorrect.', null, 422);
        }

        $user->password = Hash::make($request->string('password')->toString());
        $user->save();

        return ApiResponse::success('Password changed successfully.');
    }

    public function deactivate(Request $request)
    {
        $user = $request->user();
        $user->forceFill(['is_active' => false])->save();
        $user->tokens()->delete();

        return ApiResponse::success('Account deactivated successfully.');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? ApiResponse::success('Password reset link sent.')
            : ApiResponse::error('Unable to send password reset link.', null, 422);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(config('sparktech-auth.password.min_length', 8))],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password): void {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ApiResponse::success('Password reset successfully.')
            : ApiResponse::error('Unable to reset password.', ['status' => __($status)], 422);
    }
}