<?php

declare(strict_types=1);

namespace Sparktech\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SocialAccount extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'provider_id', 'provider_email',
        'provider_name', 'access_token', 'refresh_token', 'token_expires_at',
    ];

    protected $casts = ['token_expires_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('sparktech-auth.user_model'));
    }
}