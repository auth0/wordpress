# Change Log

## [5.2.0](https://github.com/auth0/wp-auth0/tree/5.1.0) (2023-12-11)

### Added

- feat(SDK-4734): Implement support for Back-Channel Logout  [\#882](https://github.com/auth0/wordpress/pull/882) ([evansims](https://github.com/evansims))

> **Note**
> ¹ To use this feature, an Auth0 tenant must have support for it enabled.

## [5.1.0](https://github.com/auth0/wp-auth0/tree/5.1.0) (2023-07-24)

### Added

- Organization Name support was added for Authentication API and token handling ¹

### Updated

- Bumped tested WordPress version to forthcoming 6.3.0 release.
- Bumped `auth0-php` dependency version range to `^8.7`.
- Updated telemetry to indicate `wordpress` package (previously `wp-auth0`.)

> **Note**
> ¹ To use this feature, an Auth0 tenant must have support for it enabled. This feature is not yet available to all tenants.

## [5.0.1](https://github.com/auth0/wp-auth0/tree/5.0.1) (2022-12-12)

### Fixed

- Resolves an issue that sometimes prevented the plugin from being activated on WordPress 6

## [5.0.0](https://github.com/auth0/wp-auth0/tree/5.0.0) (2022-10-28)

Introducing V5 of WP-Auth0 ("Login by Auth0"), a major redesign and upgrade to our WordPress integration plugin. V5 includes many new features and changes:

- [WordPress 6](https://wordpress.org/support/wordpress-version/version-6-0/) and [PHP 8](https://www.php.net/releases/8.0/en.php) support
- Integration with the [Auth0-PHP SDK](https://github.com/auth0/auth0-php), and access to its entire API (including Management API calls)
  High-performance background sync using [WordPress' Cron](https://developer.wordpress.org/plugins/cron/) feature
- "Flexible identifier" support, allowing users to sign in using multiple connection types without requiring extra configuration
- Expanded control over how sign-ins without matching existing WordPress accounts are handled
- Enhanced session pairing between WordPress and Auth0, including session invalidation, access token refresh, and more.

V5 represents a major step forward for our WordPress plugin, and we're excited to see what you build with it!

It's important to note, if you wrote custom theme code or plugins for your WordPress site that targeted previous versions of the plugin, you may need to adjust those themes or plugins to adapt to the new version.
