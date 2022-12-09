![wp-auth0](https://cdn.auth0.com/website/sdks/banners/wp-auth0-banner.png)

WordPress Plugin for [Auth0](https://auth0.com) Authentication

[![License](https://img.shields.io/packagist/l/auth0/auth0-php)](https://doge.mit-license.org/)

:rocket: [Getting Started](#getting-started) - :speech_balloon: [Feedback](#feedback)

## Getting Started

### Requirements

- PHP 8.0+
- Latest version of WordPress (6+)
- WP configured with databases priviledges to create tables

### Installation

#### Composer

Add the dependency to your application with [Composer](https://getcomposer.org/):

```
composer require auth0/wordpress
```

Then,

1. Log in to your WordPress site as an administrator.
2. Go to Plugins menu.
3. Look for "Login by Auth0" in the list.
4. Click Install Now, and then Activate.

#### WordPress.org

1. Log in to your WordPress site as an administrator.
2. Go to Plugins menu, then click 'Add New'.
3. Search for "Login by Auth0".
4. Click Install Now, and then Activate.

### Configure Auth0

Create a **Regular Web Application** in the [Auth0 Dashboard](https://manage.auth0.com/#/applications). Verify that the "Token Endpoint Authentication Method" is set to `POST`.

Next, configure the callback and logout URLs for your application under the "Application URIs" section of the "Settings" page:

- **Allowed Callback URLs**: The URL of your application where Auth0 will redirect to during authentication, e.g., `http://localhost:3000/callback`.
- **Allowed Logout URLs**: The URL of your application where Auth0 will redirect to after user logout, e.g., `http://localhost:3000/login`.

Note the **Domain**, **Client ID**, and **Client Secret**. These values will be used later.

### Configure the SDK

Upon activating the Auth0 WordPress plugin, you will find a new "Auth0" section on the left hand side of your administrative dashboard. This section enables you to configure the plugin.

At a minimum, you will need to configure the Domain, Client ID and Client Secret sections for the plugin to function.

We recommend testing on a staging/development site first using a separate Auth0 Application before putting the plugin live on your production site. Be sure to enable the plugin from the Auth0's plugins admin settings page for authentication with Auth0 to function.

### Plugin Database Tables

For performance reasons, V5 of the WordPress plugin as adopted it's own database tables. This means the WordPress database credentials [you have configured](https://wordpress.org/support/article/creating-database-for-wordpress/) must have appropriate priviledges to create new tables.

### Cron Configuration

It's important to configure your WordPress site's built-in background task system, [WP-Cron](https://developer.wordpress.org/plugins/cron/). This is the mechanism that V5 of the WordPress plugin keeps WordPress and Auth0 in sync.

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

<p align="center">Auth0 is an easy to implement, adaptable authentication and authorization platform. To learn more checkout <a href="https://auth0.com/why-auth0">Why Auth0?</a></p>

<p align="center">This project is licensed under the MIT license. See the <a href="./LICENSE"> LICENSE</a> file for more info.</p>
