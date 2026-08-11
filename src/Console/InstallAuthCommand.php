<?php

declare(strict_types=1);

namespace Dev\Auth\Console;

use Illuminate\Console\Command;

final class InstallAuthCommand extends Command
{
    protected $signature = 'dev-auth:install
                            {--force : Overwrite published files}';

    protected $description = 'Install the Dev Authentication package';

    public function handle(): int
    {
        $this->components->info('Installing Dev Authentication...');

        $this->call('vendor:publish', [
            '--tag' => 'dev-auth-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'dev-auth-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->components->info('Dev Authentication installed successfully.');

        return self::SUCCESS;
    }
}