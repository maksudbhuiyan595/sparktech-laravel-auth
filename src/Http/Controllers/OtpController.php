<?php

declare(strict_types=1);

namespace Dev\Auth\Http\Controllers;

use Dev\Auth\Models\AuthOtp;
use Dev\Auth\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class OtpController
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string', 'max:255'],
            'channel' => ['nullable', 'in:email,sms'],
            'purpose' => ['nullable', 'in:verification,password_reset,login'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        AuthOtp::query()
            ->where('identifier', $request->string('identifier')->toString())
            ->where('channel', $request->input('channel', 'email'))
            ->where('purpose', $request->input('purpose', 'verification'))
            ->whereNull('verified_at')
            ->delete();

        AuthOtp::query()->create([
            'identifier' => $request->string('identifier')->toString(),
            'channel' => $request->input('channel', 'email'),
            'purpose' => $request->input('purpose', 'verification'),
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes((int) config('dev-auth.otp.expires_in', 5)),
        ]);

        // Delivery is intentionally abstracted for the next milestone.
        // Never return OTP in production responses.
        $response = [
            'expires_in' => (int) config('dev-auth.otp.expires_in', 5) * 60,
        ];

        if (app()->environment(['local', 'testing'])) {
            $response['development_otp'] = $code;
        }

        return ApiResponse::success('OTP generated successfully.', $response);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string', 'max:255'],
            'code' => ['required', 'digits:6'],
            'channel' => ['nullable', 'in:email,sms'],
            'purpose' => ['nullable', 'in:verification,password_reset,login'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors());
        }

        $otp = AuthOtp::query()
            ->where('identifier', $request->string('identifier')->toString())
            ->where('channel', $request->input('channel', 'email'))
            ->where('purpose', $request->input('purpose', 'verification'))
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired()) {
            return ApiResponse::error('OTP is invalid or expired.', null, 422);
        }

        if ($otp->attempts >= (int) config('dev-auth.otp.max_attempts', 5)) {
            return ApiResponse::error('Maximum OTP attempts exceeded.', null, 429);
        }

        if (! hash_equals($otp->code_hash, hash('sha256', $request->string('code')->toString()))) {
            $otp->increment('attempts');

            return ApiResponse::error('Invalid OTP.', null, 422);
        }

        $otp->forceFill(['verified_at' => now()])->save();

        return ApiResponse::success('OTP verified successfully.');
    }
}