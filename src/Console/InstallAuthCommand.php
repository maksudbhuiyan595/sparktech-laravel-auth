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

    protected $description = 'Install Sparktech Authentication and merge its API routes into routes/api.php';

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

        $this->mergeApiRoutes($files);

        $this->components->info('Sparktech Authentication installed successfully.');
        $this->components->info('Sparktech Auth routes have been merged into routes/api.php.');

        return self::SUCCESS;
    }

    private function mergeApiRoutes(Filesystem $files): void
    {
        $source = dirname(__DIR__, 2) . '/routes/api.php';
        $destination = base_path('routes/api.php');

        if (! $files->exists($source)) {
            throw new RuntimeException('Sparktech Auth package routes/api.php was not found.');
        }

        $packageRoutes = $files->get($source);

        if (! $files->exists($destination)) {
            $files->ensureDirectoryExists(dirname($destination));
            $files->put($destination, trim($packageRoutes) . PHP_EOL);
            $this->components->info('Created routes/api.php with Sparktech Auth routes.');
            return;
        }

        $existing = $files->get($destination);

        if (
            str_contains($existing, 'Sparktech\\Auth\\Http\\Controllers\\CoreAuthController')
            || str_contains($existing, 'sparktech/laravel-auth')
        ) {
            $this->components->warn('Sparktech Auth routes already exist in routes/api.php; skipped.');
            return;
        }

        if ($this->option('force')) {
            $files->put($destination, trim($packageRoutes) . PHP_EOL);
            $this->components->info('Replaced routes/api.php with Sparktech Auth routes.');
            return;
        }

        $merged = $this->mergePhpRouteFiles($existing, $packageRoutes);
        $files->put($destination, $merged);

        $this->components->info('Merged Sparktech Auth imports and routes into routes/api.php.');
    }

    private function mergePhpRouteFiles(string $existing, string $package): string
    {
        $existing = preg_replace('/^\s*<\?php\s*/', '', $existing, 1) ?? $existing;
        $package = preg_replace('/^\s*<\?php\s*/', '', $package, 1) ?? $package;

        // A second declare(strict_types=1) cannot be placed after the existing
        // application code/imports, so package-level strict_types is omitted.
        $package = preg_replace('/^\s*declare\(strict_types\s*=\s*1\);\s*/', '', $package, 1) ?? $package;

        [$packageUses, $packageBody] = $this->extractUseStatements($package);
        $existingUses = $this->extractUseStatements($existing)[0];

        $mergedUses = $existingUses;
        foreach ($packageUses as $use) {
            if (! in_array($use, $mergedUses, true)) {
                $mergedUses[] = $use;
            }
        }

        $existingWithoutUses = $this->removeUseStatements($existing);

        // Keep Laravel's existing code first, then add package imports before
        // the first executable statement so the resulting file is valid PHP.
        $header = "<?php\n\n";
        $allUses = $mergedUses;
        if ($allUses !== []) {
            $header .= implode("\n", $allUses) . "\n\n";
        }

        $body = trim($existingWithoutUses);
        $packageBody = trim($packageBody);

        if ($body !== '' && $packageBody !== '') {
            $body .= "\n\n" . $packageBody;
        } elseif ($packageBody !== '') {
            $body = $packageBody;
        }

        return $header . $body . "\n";
    }

    /** @return array{0: list<string>, 1: string} */
    private function extractUseStatements(string $content): array
    {
        preg_match_all('/^use\s+[^;]+;\s*$/m', $content, $matches);
        $uses = array_values(array_unique(array_map('trim', $matches[0] ?? [])));

        return [$uses, $this->removeUseStatements($content)];
    }

    private function removeUseStatements(string $content): string
    {
        return preg_replace('/^use\s+[^;]+;\s*\n?/m', '', $content) ?? $content;
    }
}
