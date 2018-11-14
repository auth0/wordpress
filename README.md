# Login by Auth0

![Login by Auth0](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

This plugin replaces standard WordPress login forms with one powered by Auth0 that enables social, passwordless, and enterprise connection login as well as additional security, multifactor auth, and user statistics. Please see the [WP.org plugin page](https://wordpress.org/plugins/auth0/) for more details on functionality. 

**Please note:** As of 9/28/2018, the `master` branch is now the latest reviewed and tested functionality rather than the latest WP.org release. The code here should be considered pre-release and should not be used on production.

[![WordPress plugin downloads](https://img.shields.io/wordpress/plugin/dt/auth0.svg)](https://wordpress.org/plugins/auth0/)
[![WordPress plugin rating](https://img.shields.io/wordpress/plugin/r/auth0.svg)](https://wordpress.org/plugins/auth0/)
[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/auth0.svg)](https://wordpress.org/plugins/auth0/)

## Table of Contents

- [Documentation](#documentation)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Contribution](#contribution)
- [Support + Feedback](#support--feedback)
- [Vulnerability Reporting](#vulnerability-reporting)
- [What is Auth0](#what-is-auth0)
- [License](#license)

## Documentation

* [Installation](https://auth0.com/docs/cms/wordpress/installation)
* [Configuration](https://auth0.com/docs/cms/wordpress/configuration)
* [Troubleshooting](https://auth0.com/docs/cms/wordpress/troubleshoot)
* [Extending](https://auth0.com/docs/cms/wordpress/extending)

## Installation

Please see the [installation docs](https://auth0.com/docs/cms/wordpress/installation) for detailed instructions on how to get started with Login by Auth0.

## Getting Started

Please see the [configuration docs](https://auth0.com/docs/cms/wordpress/configuration) for instructions on how to configure Login by Auth0 for your site. Once configured, you'll want to test:

- Existing user login
- New user signup (if allowed)
- SSO login (if used)
- Additional features like MFA, user migration, etc.

We recommend testing on a staging/development site first using a separate Auth0 Application before putting the plugin live on your production site. See the **[Support](#support--feedback)** section below if you have any questions or issues during setup.

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
