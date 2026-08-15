# Changelog

## 1.0.4 - 2026-08-15
- Moved package route file from `routes/api.php` to `routes/auth.php`.
- Package service provider loads routes internally; application `routes/api.php` is never modified.
- Installer only publishes package config and migrations.
- Authentication endpoints remain under `/api/auth/*` by default.


## 1.0.3 - 2026-08-15
- Package routes are now registered automatically by the service provider.
- `sparktech-auth:install` no longer creates, replaces, or appends to the application's `routes/api.php`.
- Application `routes/api.php` stays clean and contains only application-owned routes.
- Added configurable `api_prefix` (default: `api`).
- Package authentication routes are available under `/api/auth/*` by default.
- Package health endpoint version updated to 1.0.3.

## 1.0.2 - 2026-08-12
- Clean Laravel 12 package release for PHP 8.2+.
- Fixed package namespace/autoload metadata to use `Sparktech\Auth`.
- Fixed Laravel auto-discovery provider metadata.
- Kept Pest 3.x for PHP 8.2 compatibility.
- Added Composer plugin permission configuration for Pest.
- Fixed package boot test route loading and version assertions.
- Includes authentication controllers, routes, migrations, service provider, and install command.

## 1.0.0 - 2026-08-12
- Initial Laravel 12 authentication foundation release.
