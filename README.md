# Sparktech Laravel Auth

`sparktech/laravel-auth` is a private, reusable authentication foundation for Laravel applications.

## Version

**1.0.2 — Laravel 12 Authentication Foundation**

## Current milestone

This release establishes the package foundation:

- Composer package metadata
- PSR-4 autoloading
- Laravel package auto-discovery
- Service Provider
- Config file
- Publishable config
- Publishable migrations directory
- Package installation command
- Package route registration
- Health endpoint
- Initial test/tooling configuration

Core authentication is now included:

- Register
- Login
- Logout
- Current user (`/me`)
- Password hashing
- Validation
- Laravel Sanctum token authentication
- Token revocation
- Logout all devices
- Device token names
- OTP send/verify foundation
- OTP expiration and attempt limits
- Social provider contract foundation

## Requirements

- PHP 8.2+
- Laravel 12.x

## Local development

From a Laravel application, add the package as a Composer path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../sparktech-laravel-auth"
        }
    ]
}
```

Then:

```bash
composer require sparktech/laravel-auth:@dev
php artisan sparktech-auth:install
```

The package health endpoint will be available at:

```text
GET /api/auth/health
```

Expected response:

```json
{
    "success": true,
    "package": "sparktech/laravel-auth",
    "version": "1.0.2",
    "status": "ready"
}
```

## Package commands

```bash
php artisan sparktech-auth:install
```

## Important integration note

The package uses the host application's configured user model (`sparktech-auth.user_model`) and expects the standard Laravel `users` table with `name`, `email`, and `password` fields.

Run migrations after installing:

```bash
php artisan migrate
```


## Sanctum setup

Add the Sanctum trait to the host application's `App\Models\User` model:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

Then publish/run Sanctum migrations in the host application:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

The package uses `auth:sanctum` for protected routes.

## Core authentication endpoints

```text
POST /api/auth/register
POST /api/auth/login
POST /api/auth/email/verify-otp
POST /api/auth/email/resend-otp
POST /api/auth/logout
POST /api/auth/logout-all
GET  /api/auth/me
POST /api/auth/change-password
POST /api/auth/forgot-password
POST /api/auth/reset-password
POST /api/auth/deactivate
```

#
## Registration email OTP flow

Registration now works as:

```text
POST /api/auth/register
        ↓
Create user
        ↓
Generate 6-digit OTP
        ↓
Store hashed OTP + expiry
        ↓
Send OTP by email
        ↓
POST /api/auth/email/verify-otp
        ↓
Mark email_verified_at
        ↓
Issue Sanctum token
```

Resend:

```text
POST /api/auth/email/resend-otp
```

Configure Laravel's normal `MAIL_*` environment variables in the host application's `.env`. The package uses Laravel's notification mail channel, so SMTP/Mailgun/SES/etc. can be configured by the host application.

## Social login

```text
GET /api/auth/social/google/redirect
GET /api/auth/social/google/callback

GET /api/auth/social/apple/redirect
GET /api/auth/social/apple/callback
```

The provider system is extensible for Facebook, GitHub, GitLab, LinkedIn and Bitbucket.

### Important User model requirement

For account deactivation, add `is_active` to the users table. This package includes a migration for it.

For Sanctum:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

## API endpoints

```text
POST /api/auth/register
POST /api/auth/login
POST /api/auth/email/verify-otp
POST /api/auth/email/resend-otp
POST /api/auth/logout
POST /api/auth/logout-all
GET  /api/auth/me
POST /api/auth/otp/send
POST /api/auth/otp/verify
GET  /api/auth/health
```

For `/logout` and `/me`, send:

```text
Authorization: Bearer YOUR_TOKEN
```

## Package API route behavior

The package keeps authentication routes in its `routes/api.php` file. The route file intentionally does **not** add the `api` prefix. When `php artisan sparktech-auth:install` publishes these routes into the host application's `routes/api.php`, Laravel's API route configuration provides the `/api` prefix.

Therefore, the default endpoints are:

```text
/api/auth/register
/api/auth/login
/api/auth/logout
/api/auth/me
/api/auth/health
```

The installer adds the package routes to an existing `routes/api.php`, or creates that file when the Laravel API stack is not yet enabled. Use `--force` only when you intentionally want to replace the existing API routes file.

## Security

This is a proprietary/private package. Do not commit application credentials, OAuth secrets, API keys, or production `.env` files to this repository.
