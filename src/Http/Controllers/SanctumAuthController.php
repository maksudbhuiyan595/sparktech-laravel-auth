<?php

declare(strict_types=1);

namespace Sparktech\Auth\Http\Controllers;

use Sparktech\Auth\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final class SanctumAuthController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $modelClass = config('sparktech-auth.user_model');
        $user = new $modelClass();
        $user->name = $request->string('name')->toString();
        $user->email = $request->string('email')->toString();
        $user->password = Hash::make($request->string('password')->toString());
        $user->save();

        $token = $user->createToken(
            $request->string('device_name', 'web')->toString()
        )->plainTextToken;

        return ApiResponse::success('Registration successful.', [
            'user' => $user->fresh(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $modelClass = config('sparktech-auth.user_model');
        $user = $modelClass::query()
            ->where('email', $request->string('email')->toString())
            ->first();

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

        return ApiResponse::success('All sessions revoked successfully.');
    }

    public function me(Request $request)
    {
        return ApiResponse::success('Authenticated user.', [
            'user' => $request->user(),
        ]);
    }
}