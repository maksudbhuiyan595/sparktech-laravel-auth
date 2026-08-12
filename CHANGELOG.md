# Changelog

## 1.3.1 - 2026-08-12
- Fixed the package health test version assertion.
- Kept API routes publishable to the host application `routes/api.php` without hard-coding the `api` prefix.
- Updated development tooling to Pest 3.x for PHP 8.2 compatibility.
- Added Composer plugin permission configuration for Pest.

## 1.3.0 - 2026-08-12
- API routes are published into the consuming Laravel application `routes/api.php` by `sparktech-auth:install`.
- Removed automatic package route registration to avoid duplicate routes.
- The package no longer applies an `api` prefix itself. Laravel's API route configuration provides `/api`.
- Laravel 12 `install:api` is invoked when available.
