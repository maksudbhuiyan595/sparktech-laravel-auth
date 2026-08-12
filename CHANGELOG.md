# Changelog

## [0.4.0] - 2026-08-11

### Added

- Complete core authentication route set.
- Remember-me request support.
- Change password.
- Forgot password.
- Reset password.
- Account deactivation.
- Laravel Socialite integration foundation.
- Google social login redirect/callback.
- Apple social login redirect/callback.
- Extensible social provider architecture.
- Sanctum remains the token layer.


## [0.3.0] - 2026-08-11

### Changed

- Switched authentication token management to Laravel Sanctum.
- Added Sanctum `auth:sanctum` middleware usage.
- Added logout-all-devices endpoint.
- Added OTP generation and verification foundation.
- Added social provider contract foundation.
- Added social account and OTP migrations.


## [0.2.0] - 2026-08-10

### Added

- Register endpoint.
- Login endpoint.
- Logout endpoint.
- Current user endpoint.
- Secure hashed bearer tokens.
- Token expiration support.
- Authentication middleware.
- Authentication token migration.
- Consistent API response structure.


## [0.1.1] - 2026-08-10

### Changed

- Changed the runtime requirement to PHP 8.2+.
- Aligned Laravel Illuminate dependencies with Laravel 12.


All notable changes to `sparktech/laravel-auth` are documented here.

## [0.1.0] - 2026-08-10

### Added

- Initial Composer package structure.
- Laravel service provider.
- Package auto-discovery metadata.
- Config file.
- Installation command.
- Route registration.
- Health endpoint.
- Initial documentation.
