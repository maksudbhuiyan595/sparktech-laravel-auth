<?php

declare(strict_types=1);

namespace Sparktech\Auth\Tests\Feature;

use Sparktech\Auth\Tests\TestCase;

final class PackageBootTest extends TestCase
{
    public function test_package_boots(): void
    {
        $this->getJson('/auth/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'package' => 'sparktech/laravel-auth',
                'version' => '1.0.1',
                'status' => 'ready',
            ]);
    }
}