# Login by Auth0

![Login by Auth0](https://ps.w.org/auth0/assets/banner-772x250.png)

This plugin replaces standard WordPress login forms with one powered by Auth0 that enables social, passwordless, and enterprise connection login as well as additional security, multifactor auth, and user statistics. Please see the [WP.org plugin page](https://wordpress.org/plugins/auth0/) for more details on functionality.

**Important note:** The `master` branch is now the latest reviewed and tested functionality for version 4.0.0 and may contain breaking changes. Please see the [4.0.0 milestone, closed tab](https://github.com/auth0/wp-auth0/milestone/15?closed=1) for a list of merged changes. The latest [WP.org](https://wordpress.org/plugins/auth0/) release can be found in the [`wordpress-org-plugin`](https://github.com/auth0/wp-auth0/tree/wordpress-org-plugin) branch.

[![CircleCI](https://img.shields.io/circleci/project/github/auth0/wp-auth0/master.svg)](https://circleci.com/gh/auth0/wp-auth0)
[![WordPress plugin downloads](https://img.shields.io/wordpress/plugin/dt/auth0.svg)](https://wordpress.org/plugins/auth0/)
[![WordPress plugin rating](https://img.shields.io/wordpress/plugin/r/auth0.svg)](https://wordpress.org/plugins/auth0/)
[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/auth0.svg)](https://wordpress.org/plugins/auth0/)

## Table of Contents

- [Documentation](#documentation)
- [Installation](#installation)
- [Getting Started](#getting-started)
  - [Organizations (Closed Beta)](#organizations-closed-beta)
- [Contribution](#contribution)
- [Support + Feedback](#support--feedback)
- [Vulnerability Reporting](#vulnerability-reporting)
- [What is Auth0](#what-is-auth0)
- [License](#license)

## Documentation

- [Installation](https://auth0.com/docs/cms/wordpress/installation)
- [Configuration](https://auth0.com/docs/cms/wordpress/configuration)
- [Troubleshooting](https://auth0.com/docs/cms/wordpress/troubleshoot)
- [Extending](https://auth0.com/docs/cms/wordpress/extending)

## Installation

Please see the [installation docs](https://auth0.com/docs/cms/wordpress/installation) for detailed instructions on how to get started with Login by Auth0.

## Getting Started

Please see the [configuration docs](https://auth0.com/docs/cms/wordpress/configuration) for instructions on how to configure Login by Auth0 for your site. Once configured, you'll want to test:

- Existing user login
- New user signup (if allowed)
- SSO login (if used)
- Additional features like MFA, user migration, etc.

We recommend testing on a staging/development site first using a separate Auth0 Application before putting the plugin live on your production site. See the **[Support](#support--feedback)** section below if you have any questions or issues during setup.

### Organizations (Closed Beta)

Organizations is a set of features that provide better support for developers who build and maintain SaaS and Business-to-Business (B2B) applications.

Using Organizations, you can:

- Represent teams, business customers, partner companies, or any logical grouping of users that should have different ways of accessing your applications, as organizations.
- Manage their membership in a variety of ways, including user invitation.
- Configure branded, federated login flows for each organization.
- Implement role-based access control, such that users can have different roles when authenticating in the context of different organizations.
- Build administration capabilities into your products, using Organizations APIs, so that those businesses can manage their own organizations.

Note that Organizations is currently only available to customers on our Enterprise and Startup subscription plans.

#### Configure WordPress to use an Organization

Adding Organization support to your WordPress installation is simple. Configure WordPress and the Auth0 plugin as normally instructed, and then follow these additional steps:

1. Open your [Auth0 dashboard](https://manage.auth0.com/dashboard).
2. In your Application settings, ensure your 'Application Login URI' points to your WordPress installation's URL.
3. Create a new Organization.
4. Copy the ID of your new organization, beginning with 'org\_'.
5. Open the Auth0 WordPress plugin settings page within your WordPress installation, and navigate to the Basic tab.
6. Paste the Organization ID into the 'Organization' field.
7. Save your changes.

If you have existing users of your WordPress blog and Auth0, you should add those users to your new Organization using the Auth0 dashboard. Ensure 'membership on authentication' is enabled for the Organization's Connection to automatically add them upon signing in.

With an organization configured, users logging into your WordPress installation will see your Universal Login Page customized for the Organization. You can further customize it's appearance from the Auth0 dashboard.

Organizations also support invitations. To use this feature, navigate to the Invitations tab for the corresponding Organization on your Auth0 dashboard and click 'invite member.' When the user clicks their invitation link, they'll be redirected to your WordPress installation, and then prompted to create their account or sign in.

## Contribution

We appreciate feedback and contribution to this plugin! Before you get started, please see the following:

- [Auth0's general contribution guidelines](https://github.com/auth0/open-source-template/blob/master/GENERAL-CONTRIBUTING.md)
- [Auth0's code of conduct guidelines](https://github.com/auth0/open-source-template/blob/master/CODE-OF-CONDUCT.md)
- [This repo's contribution guidelines](CONTRIBUTION.md)

## Support + Feedback

Include information on how to get support. Consider adding:

- Use [Issues](https://github.com/auth0/wp-auth0/issues) for code-level support
- Use [Community](https://community.auth0.com/tags/wordpress) for usage, questions, and specific cases
- You can also use the [WP.org support forum](https://wordpress.org/support/plugin/auth0) for questions

## Vulnerability Reporting

Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## What is Auth0?

Auth0 helps you to easily:

- implement authentication with multiple identity providers, including social (e.g., Google, Facebook, Microsoft, LinkedIn, GitHub, Twitter, etc), or enterprise (e.g., Windows Azure AD, Google Apps, Active Directory, ADFS, SAML, etc.)
- log in users with username/password databases, passwordless, or multi-factor authentication
- link multiple user accounts together
- generate signed JSON Web Tokens to authorize your API calls and flow the user identity securely
- access demographics and analytics detailing how, when, and where users are logging in
- enrich user profiles from other data sources using customizable JavaScript rules

[Why Auth0?](https://auth0.com/why-auth0)

## License

Login by Auth0 is licensed under GPLv2 - [LICENSE](LICENSE)
