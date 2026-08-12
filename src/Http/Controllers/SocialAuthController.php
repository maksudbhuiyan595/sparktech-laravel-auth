<?php

declare(strict_types=1);

namespace Sparktech\Auth\Http\Controllers;

use Sparktech\Auth\Models\SocialAccount;
use Sparktech\Auth\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController
{
    private function provider(string $provider): void
    {
        $allowed = array_keys(config('sparktech-auth.social.providers', []));

        abort_unless(in_array($provider, $allowed, true), 404, 'Social provider not configured.');

        abort_unless(
            config("sparktech-auth.social.providers.$provider.enabled", false),
            503,
            'Social provider is disabled.'
        );
    }

    public function redirect(string $provider)
    {
        $this->provider($provider);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        $this->provider($provider);

        $socialUser = Socialite::driver($provider)->stateless()->user();
        $account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($account) {
            $user = $account->user;
        } else {
            $model = config('sparktech-auth.user_model');
            $email = $socialUser->getEmail();

            $user = $email ? $model::query()->where('email', $email)->first() : null;

            if (! $user) {
                $user = new $model();
                $user->name = $socialUser->getName() ?: $socialUser->getNickname() ?: ucfirst($provider) . ' User';
                if ($email) {
                    $user->email = $email;
                } else {
                    $user->email = $provider . '_' . $socialUser->getId() . '@social.local';
                }
                $user->password = Hash::make(Str::random(64));
                $user->save();
            }

            SocialAccount::query()->updateOrCreate(
                ['provider' => $provider, 'provider_id' => $socialUser->getId()],
                [
                    'user_id' => $user->getKey(),
                    'provider_email' => $socialUser->getEmail(),
                    'provider_name' => $socialUser->getName(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'token_expires_at' => $socialUser->expiresIn ? now()->addSeconds((int) $socialUser->expiresIn) : null,
                ]
            );
        }

        $token = $user->createToken($provider)->plainTextToken;

        return ApiResponse::success('Social login successful.', [
            'provider' => $provider,
            'user' => $user->fresh(),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}