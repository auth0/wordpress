![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Login by Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WordPress instances. Powered by Auth0.

* [WordPress.org plugin page](https://wordpress.org/plugins/auth0/)
* [Documentation (installation, configuration, more)](https://auth0.com/docs/cms/wordpress)

## Installation

Please see the [Installation docs](https://auth0.com/docs/cms/wordpress/installation) for detailed instructions.

## Extending the plugin

Please see the [Extending page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/extending) for documentation on existing hooks.

We're happy to review and approve new filters and actions that help you integrate even further in this plugin. Please
 see the Contributing section at the bottom of this page.

## API authentication

Please see the [JWT Authentication docs](https://auth0.com/docs/cms/wordpress/jwt-authentication) for more information.

## Contributing

**Thank you in advance!**

* All PRs must use the `dev` branch as a base
* Change the least amount of code to achieve the goal; PRs with lots of whitespace changes and abstraction combined 
with a feature add or bug fix are difficult to review and may be rejected
* Use the latest version of WordPress and turn `WP_DEBUG` on
* If other plugins are installed during testing that might affect behavior, please list those in the PR description
* Make sure to test against the lowest PHP version supported (see "Requires PHP" [here](https://github.com/auth0/wp-auth0/blob/master/readme.txt#L5)) 

### How to install and configure WordPress for testing

Currently, we don't have any special way to setup WordPress to test. That said, we try to cover as many use cases as possible and one way to do that is with a "headless" WordPress install (core WordPress files in a separate directory). Here's a quick and easy way to set that up on the command line:

1. `mkdir wp-doc-root; cd wp-doc-root; mkdir wp;`
2. `mkdir wp-content; mkdir wp-content/themes; mkdir wp-content/plugins;`
3. `git clone https://github.com/WordPress/WordPress.git wp; # takes a while`
4. `cp ./wp/wp-config-sample.php ./wp-config.php; # edit to add MySQL DB info and set "WP_DEBUG" to TRUE`
5. `cp ./wp/index.php ./index.php; # replace "/wp-blog-header.php" with "/wp/wp-blog-header.php"`
6. `git clone https://github.com/joshcanhelp/auth0-wp-test.git ./wp-content/themes/auth0-wp-test`

### How to build the initial setup assets?

You need to install the stylus tool and run this command (inside /assets/css):

```
$ stylus -c -o initial-setup.css initial-setup/main.styl
```

To watch and auto-compile it while working:

```
$ stylus -w -o initial-setup.css initial-setup/main.styl
```

## Issue Reporting

If you have found a bug or if you have a feature request, please report them at this repository issues section. Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## Author

* [Auth0](auth0.com)

## License

This project is licensed under the MIT license. See the [LICENSE](LICENSE) file for more info.
