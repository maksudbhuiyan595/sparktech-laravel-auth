<?php

declare(strict_types=1);

namespace Dev\Auth\Tests;

use Dev\Auth\DevAuthServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DevAuthServiceProvider::class,
        ];
    }
}