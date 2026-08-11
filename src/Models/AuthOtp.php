<?php

declare(strict_types=1);

namespace Dev\Auth\Models;

use Illuminate\Database\Eloquent\Model;

final class AuthOtp extends Model
{
    protected $table = 'auth_otps';

    protected $fillable = [
        'identifier', 'channel', 'purpose', 'code_hash',
        'attempts', 'expires_at', 'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}