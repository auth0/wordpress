# Change Log

## [6.0.0-beta.0](https://github.com/auth0/wp-auth0/tree/6.0.0-beta.0) (2026-08-07)
[Full Changelog](https://github.com/auth0/wp-auth0/compare/5.6.1...6.0.0-beta.0)

This is a pre-release for the upcoming 6.x major. It moves the plugin onto auth0-php v9, which rewrites the Management API. The authentication flow is unchanged, and the migration is confined to the Management API surface used by background user sync.

**Breaking Changes**

- Bumped the `auth0/auth0-php` dependency from `^8.19` to `^9.0`, which rewrites the Management API
- Raised the minimum PHP version from `8.1` to `8.2`
- `getSdk()->management()` is non-functional with auth0-php v9 and throws a `TypeError`. Use the new `getManagement()` accessor instead

**Added**

- `Plugin::getManagement()` accessor returning a v9 `ManagementClient` built from the existing plugin configuration, with automatic client credentials token management and caching
- `UPGRADING.md` 5.x to 6.x migration guide

**Fixed**

- Background user sync now retries transient Management API failures (429, 5xx) on the next cron pass instead of dropping the queued event

**Unchanged**

- The authentication flow (login, logout, callback, session handling) behaves exactly as in 5.x

## [5.6.1](https://github.com/auth0/wp-auth0/tree/5.6.1) (2026-05-05)

### Fixed

-   fix: Enforce allow_fallback setting during authentication ([kishore7snehil](https://github.com/kishore7snehil))

## [5.6.0](https://github.com/auth0/wp-auth0/tree/5.6.0) (2026-04-01)

### Fixed

-  Security fix: Resolve CVE-2026-34236

## [5.5.0](https://github.com/auth0/wp-auth0/tree/5.5.0) (2025-12-16)

### Fixed

-  Security fix: Resolve CVE-2025-68129

## [5.4.0](https://github.com/auth0/wp-auth0/tree/5.4.0) (2025-09-03)

### Fixed

-  Security fix: Resolve CVE-2025-58769

## [5.3.0](https://github.com/auth0/wp-auth0/tree/5.3.0) (2025-05-16)

### Fixed

-  Security fix: Resolve CVE-2025-47275

## [5.2.1](https://github.com/auth0/wp-auth0/tree/5.2.1) (2024-06-03)

### Fixed

-   Resolves an issue in which the fallback URI secret isn't shown. [\#903](https://github.com/auth0/wordpress/pull/903) ([HPiirainen](https://github.com/HPiirainen))
-   Resolves a compatibility issue with changes in WordPress 6.5 causing invalidated sessions. ([evansims](https://github.com/evansims))

## [5.2.0](https://github.com/auth0/wp-auth0/tree/5.2.0) (2023-12-11)

### Added

-   feat(SDK-4734): Implement support for Back-Channel Logout [\#882](https://github.com/auth0/wordpress/pull/882) ([evansims](https://github.com/evansims))

> **Note**
> ¹ To use this feature, an Auth0 tenant must have support for it enabled.

## [5.1.0](https://github.com/auth0/wp-auth0/tree/5.1.0) (2023-07-24)

### Added

-   Organization Name support was added for Authentication API and token handling ¹

### Updated

-   Bumped tested WordPress version to forthcoming 6.3.0 release.
-   Bumped `auth0-php` dependency version range to `^8.7`.
-   Updated telemetry to indicate `wordpress` package (previously `wp-auth0`.)

> **Note**
> ¹ To use this feature, an Auth0 tenant must have support for it enabled. This feature is not yet available to all tenants.

## [5.0.1](https://github.com/auth0/wp-auth0/tree/5.0.1) (2022-12-12)

### Fixed

-   Resolves an issue that sometimes prevented the plugin from being activated on WordPress 6

## [5.0.0](https://github.com/auth0/wp-auth0/tree/5.0.0) (2022-10-28)

Introducing V5 of WP-Auth0 ("Login by Auth0"), a major redesign and upgrade to our WordPress integration plugin. V5 includes many new features and changes:

-   [WordPress 6](https://wordpress.org/support/wordpress-version/version-6-0/) and [PHP 8](https://www.php.net/releases/8.0/en.php) support
-   Integration with the [Auth0-PHP SDK](https://github.com/auth0/auth0-php), and access to its entire API (including Management API calls)
    High-performance background sync using [WordPress' Cron](https://developer.wordpress.org/plugins/cron/) feature
-   "Flexible identifier" support, allowing users to sign in using multiple connection types without requiring extra configuration
-   Expanded control over how sign-ins without matching existing WordPress accounts are handled
-   Enhanced session pairing between WordPress and Auth0, including session invalidation, access token refresh, and more.

V5 represents a major step forward for our WordPress plugin, and we're excited to see what you build with it!

It's important to note, if you wrote custom theme code or plugins for your WordPress site that targeted previous versions of the plugin, you may need to adjust those themes or plugins to adapt to the new version.
