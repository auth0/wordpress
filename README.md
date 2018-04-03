![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Login by Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WordPress instances. Powered by Auth0.

* [WordPress.org plugin page](https://wordpress.org/plugins/auth0/)
* [Documentation (installation, configuration, more)](https://auth0.com/docs/cms/wordpress)

## Installation

Please see the [Installation docs](https://auth0.com/docs/cms/wordpress/installation) for detailed instructions.

## Extending the plugin

[Please see the Extending page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/extending)

We're happy to review and approve new filters and actions that help you integrate even further in this plugin. Please
 see the Contributing section at the bottom of this page.

## API authentication

Please see the [JWT Authentication docs](https://auth0.com/docs/cms/wordpress/jwt-authentication) for more information.

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
