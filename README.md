![WordPress by Auth0](https://cdn.auth0.com/website/sdks/banners/wp-auth0-banner.png)

WordPress Plugin for [Auth0](https://auth0.com) Authentication

[![License](https://img.shields.io/packagist/l/auth0/auth0-php)](https://doge.mit-license.org/)

:rocket: [Getting Started](#getting-started) - :speech_balloon: [Feedback](#feedback)

## Getting Started

### Requirements

- PHP 8.0+
- [Most recent version of WordPress](https://wordpress.org/news/category/releases/)
- WordPress configured with database privileges allowing database table creation

> Please review our [support policy](#support-policy) to learn when language and framework versions will exit support in the future.

### Installation

<!-- // Disabled while we complete this distribution configuration
#### Release Package
Releases are available from the Github repository [github.com/auth0/wordpress/releases](https://github.com/auth0/wordpress/releases), packaged as ZIP archives. Every release has an accompanying signature file for verification, if desired.

<details>
<summary><b>Verify a release signature with OpenSSL (recommended)</b></summary>

1. Download the public siging key from this repository
2. Put the repository's public signing key, the release's ZIP archive, and the release's signature file (ending in `.sign`) in the same directory.
3. Run the following command, substituting `RELEASE` with the filename of the release you downloaded:

```bash
openssl dgst -verify signing.key.pub -keyform PEM -sha256 -signature RELEASE.zip.sign -binary RELEASE.zip
```

'Verified OK' should be returned. If this is not the case, do not proceed with the installation.
</details>

1. Open your WordPress Dashboard, then click 'Plugins', and then 'Add New'.
2. Find the 'Upload Plugin' function at the top of the page, and use it to upload the release package you downloaded.

> **Note** Alternatively, you can extract the release package to your WordPress installation's `wp-content/plugins` directory.
-->

#### Composer
[Composer](https://getcomposer.org/) installations are fully supported. This approach is preferred when using [Bedrock](https://roots.io/bedrock/) or [WordPress Core](https://github.com/johnpbloch/wordpress-core-installer) configurations.

- Most [WPackagist](https://wpackagist.org/) configurations (like Bedrock or Core) should have this command run from thier WordPress' root directory.
- Otherwise, this command should be run from the `wp-content/plugins` sub-directory.

```
composer require symfony/http-client nyholm/psr7 auth0/wordpress:^5.0
```

> **Note**  When installing with Composer, you will also need to install [PSR-18](https://packagist.org/providers/psr/http-client-implementation) and [PSR-17](https://packagist.org/providers/psr/http-factory-implementation) compatible suppoty libraries. The above command includes some well known defaults, but any libraries compatible with those PSRs will work.


<!-- // Disabled while we complete this distribution configuration
#### WordPress Dashboard

Installation from your WordPress dashboard is also supported. This approach first installs a small setup script that will verify that your host environment is compatible. Afterward, the latest plugin release will be downloaded from the GitHub repository, have it's file signature verified, and ultimately installed.

- Open your WordPress Dashboard.
- Click 'Plugins", then 'Add New', and search for 'Auth0'.
- Choose 'Install Now' to install the plugin.
-->

### Acivation

Once the package is installed, you will need to activate the plugin for use with your WordPress site.

1. Open your Dashboard.
2. Select 'Plugins' from the sidebar, and then 'Installed Plugins'.
3. Choose 'Activate' under the Auth0 plugin's name.

### Configure Auth0

Create a **Regular Web Application** in the [Auth0 Dashboard](https://manage.auth0.com/#/applications). Verify that the "Token Endpoint Authentication Method" is set to `POST`.

Next, configure the callback and logout URLs for your application under the "Application URIs" section of the "Settings" page:

- **Allowed Callback URLs**: The URL of your application where Auth0 will redirect to during authentication, e.g., `http://localhost:3000/callback`.
- **Allowed Logout URLs**: The URL of your application where Auth0 will redirect to after the user logout, e.g., `http://localhost:3000/login`.

Note the **Domain**, **Client ID**, and **Client Secret**. These values will be used later.

### Configure the SDK

Upon activating the Auth0 WordPress plugin, you will find a new "Auth0" section on the left-hand side of your WordPress Dashboard. This section enables you to configure the plugin.

At a minimum, you will need to configure the Domain, Client ID, and Client Secret sections for the plugin to function.

We recommend testing on a staging/development site using a separate Auth0 Application before putting the plugin live on your production site. Be sure to enable the plugin from the Auth0's plugins admin settings page for authentication with Auth0 to function.

### Plugin Database Tables

For performance reasons, V5 of the WordPress plugin has adopted its own database tables. This means the WordPress database credentials [you have configured](https://wordpress.org/support/article/creating-database-for-wordpress/) must have appropriate privileges to create new tables.

### Cron Configuration

It's essential to configure your WordPress site's built-in background task system, [WP-Cron](https://developer.wordpress.org/plugins/cron/). This is the mechanism by which the plugin keeps WordPress and Auth0 in sync. If this is not enabled, changes within WordPress may not be reflected fully on Auth0, and vice versa.

## Support Policy

- Our PHP version support window mirrors the [PHP release support schedule](https://www.php.net/supported-versions.php). Our support for PHP versions ends when they stop receiving security fixes.
- As Automattic's stated policy is "security patches are backported when possible, but this is not guaranteed", we only support [the latest release](https://wordpress.org/news/category/releases/) marked as ["actively supported"](https://endoflife.date/wordpress) by Automattic.

| Plugin Version | WordPress Version | PHP Version | Support Ends |
| -------------- | ----------------- | ----------- | ------------ |
| 5              | 6                 | 8.2         | Dec 2025     |
|                |                   | 8.1         | Nov 2024     |
|                |                   | 8.0         | Nov 2023     |

Deprecations of EOL'd language, or framework versions are not considered a breaking change. Legacy applications will stop receiving updates from us but will continue to function on those unsupported SDK versions. Please ensure your PHP and WordPress environments always remain up to date.

## Feedback

### Contributing

We appreciate feedback and contribution to this repo! Before you get started, please see the following:

- [Auth0's general contribution guidelines](https://github.com/auth0/open-source-template/blob/master/GENERAL-CONTRIBUTING.md)
- [Auth0's code of conduct guidelines](https://github.com/auth0/open-source-template/blob/master/CODE-OF-CONDUCT.md)

### Raise an issue

To provide feedback or report a bug, [please raise an issue on our issue tracker](https://github.com/auth0/wp-auth0/issues).

### Vulnerability Reporting

Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

---

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: light)" srcset="https://cdn.auth0.com/website/sdks/logos/auth0_light_mode.png" width="150">
    <source media="(prefers-color-scheme: dark)" srcset="https://cdn.auth0.com/website/sdks/logos/auth0_dark_mode.png" width="150">
    <img alt="Auth0 Logo" src="https://cdn.auth0.com/website/sdks/logos/auth0_light_mode.png" width="150">
  </picture>
</p>

<p align="center">Auth0 is an easy-to-implement, adaptable authentication and authorization platform. To learn more checkout <a href="https://auth0.com/why-auth0">Why Auth0?</a></p>

<p align="center">This project is licensed under the MIT license. See the <a href="./LICENSE"> LICENSE</a> file for more info.</p>
