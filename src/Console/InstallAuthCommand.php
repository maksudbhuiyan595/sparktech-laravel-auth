<?php

declare(strict_types=1);

namespace Sparktech\Auth\Console;

use Illuminate\Console\Command;

final class InstallAuthCommand extends Command
{
    protected $signature = 'sparktech-auth:install
                            {--force : Force publish config and migrations}';

    protected $description = 'Install Sparktech Authentication without modifying the application routes/api.php file';

    public function handle(): int
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

        $this->components->info('Sparktech Authentication installed successfully.');
        $this->components->info('Package API routes are registered automatically by the service provider.');
        $this->components->info('Your application routes/api.php is not modified.');

        return self::SUCCESS;
    }
}
