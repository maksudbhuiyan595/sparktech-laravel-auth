<?php

declare(strict_types=1);

namespace Sparktech\Auth;

use Sparktech\Auth\Console\InstallAuthCommand;
use Illuminate\Support\ServiceProvider;

final class SparktechAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sparktech-auth.php',
            'sparktech-auth'
        );
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerCommands();
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/sparktech-auth.php' => config_path('sparktech-auth.php'),
        ], 'sparktech-auth-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'sparktech-auth-migrations');
    }

    private function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallAuthCommand::class,
            ]);
        }
    }
}