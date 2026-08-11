<?php

declare(strict_types=1);

namespace Dev\Auth\Tests\Feature;

use Dev\Auth\Tests\TestCase;

final class PackageBootTest extends TestCase
{
    public function test_package_boots(): void
    {
        $this->getJson('/auth/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'package' => 'dev/laravel-auth',
                'version' => '0.2.0',
                'status' => 'ready',
            ]);
    }
}