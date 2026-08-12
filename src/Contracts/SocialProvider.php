<?php

declare(strict_types=1);

namespace Sparktech\Auth\Contracts;

interface SocialProvider
{
    public function redirect(): mixed;

    public function callback(): mixed;
}