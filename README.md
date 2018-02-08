![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Login by Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WordPress instances. Powered by Auth0.

Download and install <https://wordpress.org/plugins/auth0/>
Documentation: <https://auth0.com/docs/cms/wordpress>

## Important note on 3.5

This is a major update that requires changes to your Auth0 Dashboard to be completed. You can save a new [API token](https://auth0.com/docs/api/management/v2/tokens#get-a-token-manually) in your Basic settings in wp-admin before upgrading and the changes will be made automatically during the update. Otherwise, please review your [Client Advanced Settings](https://auth0.com/docs/cms/wordpress/configuration#client-setup), specifically your Grant Types, and [authorize your Client for the Management API](https://auth0.com/docs/cms/wordpress/configuration#authorize-the-client-for-the-management-api). 

## Installation

[Please see the Installation page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/installation)

## Extending the plugin

[Please see the Extending page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/extending)

We're happy to review and approve new filters and actions that help you integrate even further in this plugin. Please
 see the Contributing section at the bottom of this page.

## API authentication

This plugin provides the ability integrate with **wp-jwt-auth** plugin to authenticate api calls via a HTTP Authorization Header.

This plugin will detect if you have wp-jwt-auth installed and active and will offer to configure it. Accepting this, will set up the client id, secret and the custom user repository.

After the user logs in via Auth0 in your Api client (ie: using Lock in a mobile app) you will get a JWT (JSON Web Token). Then you need to send this token in a HTTP header in the following way:

```
Authorization: Bearer ##jwt##
```

For example:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb250ZW50IjoiVGhpcyBpcyB5b3VyIHVzZXIgSldUIHByb3ZpZGVkIGJ5IHRoZSBBdXRoMCBzZXJ2ZXIifQ.b47GoWoY_5n4jIyG0hPTLFEQtSegnVydcvl6gpWNeUE
```

This JWT should match with a registered user in your WP installation.

You can use this feature with API's provided by plugins like **WP REST API (WP API)**.

## Contributing

Thank you in advance!

All PR should be done towards the `dev` branch.

### How to build the initial setup assets?

You need to install the stylus tool and run this command (inside /assets/css):

```
$ stylus -c -o initial-setup.css initial-setup/main.styl
```

To watch and auto-compile it while working:

```
$ stylus -w -o initial-setup.css initial-setup/main.styl
```

## Screenshots

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-1.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-2.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-3.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-4.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-5.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-6.png)

## Issue Reporting

If you have found a bug or if you have a feature request, please report them at this repository issues section. Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## Author

* [Auth0](auth0.com)

## License

This project is licensed under the MIT license. See the [LICENSE](LICENSE) file for more info.
