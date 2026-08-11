<?php

declare(strict_types=1);

namespace Dev\Auth\Services;

use Dev\Auth\Models\AuthOtp;
use Dev\Auth\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Notification;

final class OtpService
{
    public function sendEmail(string $email, string $purpose = 'verification'): void
    {
        $expires = (int) config('dev-auth.otp.expires_in', 5);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        AuthOtp::query()
            ->where('identifier', $email)
            ->where('channel', 'email')
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        AuthOtp::query()->create([
            'identifier' => $email,
            'channel' => 'email',
            'purpose' => $purpose,
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes($expires),
        ]);

        Notification::route('mail', $email)
            ->notify(new EmailOtpNotification($code, $expires));
    }

    public function verify(string $email, string $code, string $purpose = 'verification'): bool
    {
        $otp = AuthOtp::query()
            ->where('identifier', $email)
            ->where('channel', 'email')
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired()) {
            return false;
        }

        if ($otp->attempts >= (int) config('dev-auth.otp.max_attempts', 5)) {
            return false;
        }

        if (! hash_equals($otp->code_hash, hash('sha256', $code))) {
            $otp->increment('attempts');
            return false;
        }

        $otp->forceFill(['verified_at' => now()])->save();

        return true;
    }
}