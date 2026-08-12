<?php

declare(strict_types=1);

namespace Sparktech\Auth\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class InstallAuthCommand extends Command
{
    protected $signature = 'sparktech-auth:install
                            {--force : Replace the application API routes file with the package routes}';

    protected $description = 'Install Sparktech Authentication and publish its API routes into routes/api.php';

    public function handle(Filesystem $files): int
    {
        $this->components->info('Installing Sparktech Authentication...');

        $this->call('vendor:publish', [
            '--tag' => 'sparktech-auth-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'sparktech-auth-migrations',
            '--force' => $this->option('force'),
        ]);

        // Ensure Laravel's API routing is enabled. Laravel 12 projects may not
        // have routes/api.php until the API stack is installed.
       if ($this->getApplication()->has('install:api')) {
            $this->call('install:api', [
                '--force' => false,
            ]);
        }

        $this->publishApiRoutes($files);

        $this->components->info('Sparktech Authentication installed successfully.');
        $this->components->line('API routes are available from: routes/api.php');

        return self::SUCCESS;
    }

    private function publishApiRoutes(Filesystem $files): void
    {
        $source = dirname(__DIR__, 2) . '/routes/api.php';
        $destination = base_path('routes/api.php');

        if (! $files->exists($source)) {
            throw new RuntimeException('Sparktech Auth package routes/api.php was not found.');
        }

        $packageRoutes = trim($files->get($source));

        if (! $files->exists($destination)) {
            $files->ensureDirectoryExists(dirname($destination));
            $files->put($destination, $packageRoutes . PHP_EOL);
            $this->components->info('Created routes/api.php with Sparktech Auth routes.');
            return;
        }

        if ($this->option('force')) {
            $files->put($destination, $packageRoutes . PHP_EOL);
            $this->components->info('Replaced routes/api.php with Sparktech Auth routes.');
            return;
        }

        $existing = $files->get($destination);
        $marker = 'sparktech/laravel-auth';

        if (str_contains($existing, $marker) || str_contains($existing, 'Sparktech\\Auth\\Http\\Controllers\\CoreAuthController')) {
            $this->components->warn('Sparktech Auth routes already exist in routes/api.php; skipped.');
            return;
        }

        $files->append($destination, PHP_EOL . PHP_EOL . $packageRoutes . PHP_EOL);
        $this->components->info('Added Sparktech Auth routes to routes/api.php.');
    }
}
