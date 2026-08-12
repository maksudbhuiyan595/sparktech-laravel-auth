<?php

declare(strict_types=1);

namespace Sparktech\Auth\Tests;

use Sparktech\Auth\SparktechAuthServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SparktechAuthServiceProvider::class,
        ];
    }

    protected function defineRoutes($router): void
    {
        require dirname(__DIR__) . '/routes/api.php';
    }
}
