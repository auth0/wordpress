![WordPress by Auth0](https://cdn.auth0.com/website/sdks/banners/wp-auth0-banner.png)

WordPress Plugin for [Auth0](https://auth0.com) Authentication

[![License](https://img.shields.io/packagist/l/auth0/auth0-php)](https://doge.mit-license.org/)

:rocket: [Getting Started](#getting-started) - :computer: [SDK Usage](#sdk-usage) - 📆 [Support Policy](#support-policy) - :speech_balloon: [Feedback](#feedback)

## Plugin Overview

The Auth0 WordPress plugin replaces the standard WordPress login flow with a new authentication process using Auth0's Universal Login experience. This enables you to secure your WordPress site with Auth0's advanced features, such as MFA, SSO, Passwordless, PassKey, and so on.

> [!IMPORTANT]  
> This plugin is **NOT** a SDK (Software Development Kit.) We do not provide support for customizing the plugin's behavior or integrating it into WordPress in any way beyond what is expressly explained here. If you are looking for an SDK, please build a custom solution from the [Auth0-PHP SDK](https://github.com/auth0/auth0-php) instead.

## Getting Started

### Requirements

- PHP 8.1+
- [Most recent version of WordPress](https://wordpress.org/news/category/releases/)
- Database credentials with table creation permissions

> Please review our [support policy](#support-policy) on specific PHP and WordPress versions and when they may exit support in the future.

### Installation

> [!WARNING]  
> v4 of the plugin is no longer supported as of June 2023. We are no longer providing new features or bugfixes for that release. Please upgrade to v5 as soon as possible.

<!-- // Disabled while we complete this distribution configuration
#### Release Package
Releases are available from the GitHub repository [github.com/auth0/wordpress/releases](https://github.com/auth0/wordpress/releases), packaged as ZIP archives. Every release has an accompanying signature file for verification if desired.

<details>
<summary><b>Verify a release signature with OpenSSL (recommended)</b></summary>

1. Download the public signing key from this repository
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

The plugin supports installation through [Composer](https://getcomposer.org/), and is [WPackagist](https://wpackagist.org/) compatible. This approach is preferred when using [Bedrock](https://roots.io/bedrock/) or [WordPress Core](https://github.com/johnpbloch/wordpress-core-installer), but will work with virtually any WordPress installation.

When using Composer-based WordPress configurations like Bedrock, you'll usually run this command from the root WordPress installation directory. Still, it's advisable to check the documentation the project's maintainers provided for the best guidance. This command can be run from the `wp-content/plugins` sub-directory for standard WordPress installations.

```
composer require symfony/http-client nyholm/psr7 auth0/wordpress:^5.0
```

<p><details>
<summary><b>Note on Composer Dependencies</b></summary>

When installed with Composer, the plugin depends on the presence of [PSR-18](https://packagist.org/providers/psr/http-client-implementation) and [PSR-17](https://packagist.org/providers/psr/http-factory-implementation) library implementations. The `require` command above includes two such libraries (`symfony/http-client` and `nyholm/psr7`) that satisfy these requirements, but you can use any other compatible libraries that you prefer. Visit Packagist for a list of [PSR-18](https://packagist.org/providers/psr/http-client-implementation) and [PSR-17](https://packagist.org/providers/psr/http-factory-implementation) providers.

If you are using Bedrock or another Composer-based configuration, you can try installing `auth0/wordpress` without any other dependencies, as the implementations may be satisfied by other already installed packages.

> **Note** PHP Standards Recommendations (PSRs) are standards for PHP libraries and applications that enable greater interoperability and choice. You can learn more about them and the PHP-FIG organization that maintains them [here](https://www.php-fig.org/).

</details></p>

<!-- // Disabled while we complete this distribution configuration
#### WordPress Dashboard

Installation from your WordPress dashboard is also supported. This approach first installs a small setup script that will verify that your host environment is compatible. Afterward, the latest plugin release will be downloaded from the GitHub repository, have its file signature verified, and ultimately installed.

- Open your WordPress Dashboard.
- Click 'Plugins", then 'Add New,' and search for 'Auth0'.
- Choose 'Install Now' to install the plugin.
-->

### Activation

After installation, you must activate the plugin within your WordPress site:

1. Open your WordPress Dashboard.
2. Select 'Plugins' from the sidebar, and then 'Installed Plugins.'
3. Choose 'Activate' underneath the plugin's name.

### Configure Auth0

1. Sign into Auth0. If you don't have an account, [it's free to create one](https://auth0.com/signup).
2. [Open 'Applications' from your Auth0 Dashboard](https://manage.auth0.com/#/applications/create), and select 'Create Application.'
3. Choose 'Regular Web Application' and then 'Create.'
4. From the newly created application's page, select the Settings tab.

Please prepare the following information:

- Note the **Domain**, **Client ID**, and **Client Secret**, available from the newly created Application's Settings page. You will need these to configure the plugin in the next step.
- From your WordPress Dashboard's General Settings page, note your **WordPress Address** and **Site Address** URLs. We recommend you read our guidance on [common WordPress URL issues](#common-wordpress-url-issues).

Continue configuring your Auth0 application from its Settings page:

- **Allowed Callback URLs** should include the URL to your WordPress site's `wp-login.php`.
  - In most (but not all) cases, this will be your WordPress Address with `/wp-login.php` appended.
  - Please ensure your site is configured never to cache this URL, or you may see an "invalid state" error during login.
- **Allowed Web Origins** should include both your WordPress Address and Site Address URLs.
- **Allowed Logout URLs** should consist of your WordPress Address.

<p><details id="common-wordpress-url-issues">
<summary><b>Common WordPress URL Issues</b></summary>

- These must be the URLs your visitors will use to access your WordPress site. If you are using a reverse proxy, you may need to manually configure your WordPress Address and Site Address URLs to match the URL you use to access your site.
- Make sure these URLs match your site's configured protocol. When using a reverse proxy, you may need to update these to reflect serving over SSL/HTTPS.
</details></p>

<p><details>
<summary><b>Troubleshooting</b></summary>

If you're encountering issues, start by checking that your Auth0 Application is setup like so:

- **Application Type** must be set to **Regular Web Application**.
- **Token Endpoint Authentication Method** must be set to **Post**.
- **Allowed Origins (CORS)** should be blank.

Scroll down and expand the "Advanced Settings" panel, then:

- Under **OAuth**:
  - Ensure that **JsonWebToken Signature Algorithm** is set to **RS256**.
  - Check that **OIDC Conformant** is enabled.
- Under **Grant Types**:
  - Ensure that **Implicit**, **Authorization Code**, and **Client Credentials** are enabled.
  - You may also want to enable **Refresh Token**.

</details></p>

### Configure the Plugin

Upon activating the Auth0 plugin, you will find a new "Auth0" section in the sidebar of your WordPress Dashboard. This section enables you to configure the plugin in a variety of ways.

For the plugin to operate, at a minimum, you will need to configure the Domain, Client ID, and Client Secret fields. These are available from the Auth0 Application you created in the previous step. Once configured, select the "Enable Authentication" option to have the plugin begin handling authentication for you.

We recommend testing on a staging/development site using a separate Auth0 Application before putting the plugin live on your production site.

### Configure WordPress

#### Plugin Database Tables

The plugin uses dedicated database tables to guarantee high performance. When the plugin is activated, it will use the database credentials you have configured for WordPress to create these tables.

Please ensure your configured credentials have appropriate privileges to create new tables.

#### Cron Configuration

The plugin uses WordPress' [background task manager](https://developer.wordpress.org/plugins/cron/) to perform important periodic tasks. Proper synchronization between WordPress and Auth0 relies on this.

By default, WordPress' task manager runs on every page load, which is inadvisable for production sites. For best performance and reliability, please ensure you have configured WordPress to use a [cron job](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/) to run these tasks periodically instead.

## SDK Usage

The plugin is built on top of [Auth0-PHP v8](https://github.com/auth0/auth0-PHP) — Auth0's full-featured PHP SDK for Authentication and Management APIs.

For custom WordPress development, please do not extend the plugin's classes themselves, as this is not supported. Nearly all of the plugin's APIs are considered `internal` and will change over time, most likely breaking any custom extension built upon them.

Instead, please take advantage of the full PHP SDK that the plugin is built upon. You can use the plugin's `getSdk()` method to retrieve a configured instance of the SDK, ready for use. This method can be called from the plugin's global `wpAuth0()` helper, which returns the WordPress plugin itself.

```php
<?php

$plugin = wpAuth0(); // Returns an instanceof Auth0\WordPress\Plugin
   $sdk = wpAuth0()->getSdk(); // Returns an instanceof Auth0\SDK\Auth0
```

Please direct questions about developing with the Auth0-PHP SDK to the [Auth0 Community](https://community.auth0.com), and issues or feature requests to [it's respective repository](https://github.com/auth0/auth0-PHP). Documentations and examples on working with the Auth0-PHP SDKs are also available from [it's repository](https://github.com/auth0/auth0-PHP).

## Support Policy

- Our PHP version support window mirrors the [PHP release support schedule](https://www.php.net/supported-versions.php). Our support for PHP versions ends when they stop receiving security fixes.
- As Automattic's stated policy is "security patches are backported when possible, but this is not guaranteed," we only support [the latest release](https://wordpress.org/news/category/releases/) marked as ["actively supported"](https://endoflife.date/wordpress) by Automattic.

| Plugin Version | WordPress Version | PHP Version | Support Ends |
| -------------- | ----------------- | ----------- | ------------ |
| 5              | 6                 | 8.3         | Nov 2026     |
|                |                   | 8.2         | Dec 2025     |
|                |                   | 8.1         | Nov 2024     |

Composer and WordPress do not offer upgrades to incompatible versions. Therefore, we regularly deprecate support within the plugin for PHP or WordPress versions that have reached end-of-life. These deprecations are not considered breaking changes and will not result in a major version bump.

Sites running unsupported versions of PHP or WordPress will continue to function but will not receive updates until their environment is upgraded. For your security, please ensure your PHP runtime and WordPress remain up to date.

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

<p align="center">Auth0 is an easy-to-implement, adaptable authentication and authorization platform.<br />
To learn more checkout <a href="https://auth0.com/why-auth0">Why Auth0?</a></p>

<p align="center">This project is licensed under the MIT license. See the <a href="./LICENSE"> LICENSE</a> file for more info.</p>
