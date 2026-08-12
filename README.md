# Sparktech Laravel Auth

`sparktech/laravel-auth` is a private, reusable authentication foundation for Laravel applications.

## Version

**0.4.0 — Complete Core Authentication + Google/Apple Social Login Foundation**

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
            "url": "../dev-laravel-auth"
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
    "version": "0.5.0",
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
POST /auth/register
POST /auth/login
POST /auth/email/verify-otp
POST /auth/email/resend-otp
POST /auth/logout
POST /auth/logout-all
GET  /auth/me
POST /auth/change-password
POST /auth/forgot-password
POST /auth/reset-password
POST /auth/deactivate
```

#
## Registration email OTP flow

Registration now works as:

```text
POST /auth/register
        ↓
Create user
        ↓
Generate 6-digit OTP
        ↓
Store hashed OTP + expiry
        ↓
Send OTP by email
        ↓
POST /auth/email/verify-otp
        ↓
Mark email_verified_at
        ↓
Issue Sanctum token
```

Resend:

```text
POST /auth/email/resend-otp
```

Configure Laravel's normal `MAIL_*` environment variables in the host application's `.env`. The package uses Laravel's notification mail channel, so SMTP/Mailgun/SES/etc. can be configured by the host application.

## Social login

```text
GET /auth/social/google/redirect
GET /auth/social/google/callback

GET /auth/social/apple/redirect
GET /auth/social/apple/callback
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
POST /auth/register
POST /auth/login
POST /auth/email/verify-otp
POST /auth/email/resend-otp
POST /auth/logout
POST /auth/logout-all
GET  /auth/me
POST /auth/otp/send
POST /auth/otp/verify
GET  /auth/health
```

For `/logout` and `/me`, send:

```text
Authorization: Bearer YOUR_TOKEN
```

## Next milestone

Core authentication:

- User model integration
- Register
- Login
- Logout
- Validation
- Token abstraction
- Feature tests

## Security

This is a proprietary/private package. Do not commit application credentials, OAuth secrets, API keys, or production `.env` files to this repository.
