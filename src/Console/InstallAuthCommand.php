<?php

declare(strict_types=1);

namespace Sparktech\Auth\Console;

use Illuminate\Console\Command;

final class InstallAuthCommand extends Command
{
    protected $signature = 'sparktech-auth:install
                            {--force : Overwrite published files}';

    protected $description = 'Install the Sparktech Authentication package';

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

        return self::SUCCESS;
    }
}