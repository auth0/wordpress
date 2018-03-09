![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Login by Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WordPress instances. Powered by Auth0.

* [WordPress.org plugin page](https://wordpress.org/plugins/auth0/)
* [Documentation (installation, configuration, more)](https://auth0.com/docs/cms/wordpress)

## Installation

Please see the [Installation docs](https://auth0.com/docs/cms/wordpress/installation) for detailed instructions.

## API authentication

Please see the [JWT Authentication page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/jwt-authentication) for more information. 

## Extending the plugin

Please see the [Extending docs](https://auth0.com/docs/cms/wordpress/extending) for information on existing hooks.

We're happy to review and approve new filters and actions that help you integrate even further in this plugin. Please see the **Contributing** section below for more information.

## Contributing

**Thank you in advance!**

* All PRs must use the `dev` branch as a base
* Change the least amount of code to achieve the goal; PRs with lots of whitespace changes and abstraction combined with a feature add or bug fix are difficult to review and may be rejected
* Use the latest version of WordPress and turn `WP_DEBUG` on
* If other plugins are installed during testing that might affect behavior, please list those in the PR description
* Make sure to test against the lowest PHP version supported (see `Requires PHP:` [here](readme.txt#L5)) 

### How to install and configure WordPress for testing

We try to cover as many use cases as possible and one way to do that is with a "headless" WordPress install (core WordPress files in a separate directory). Here's a quick and easy way to set that up on the command line:

```bash
mkdir wp-doc-root; cd wp-doc-root; mkdir wp;
mkdir wp-content; mkdir wp-content/themes; mkdir wp-content/plugins;

git clone https://github.com/WordPress/WordPress.git wp;
# Clones the latest, released version or WordPress (takes a while)

cp ./wp/wp-config-sample.php ./wp-config.php;
# Add MySQL DB info and set "WP_DEBUG" to TRUE` in ./wp-config.php

cp ./wp/index.php ./index.php; 
# Replace "/wp-blog-header.php" with "/wp/wp-blog-header.php" in copied ./index.php
 
git clone https://github.com/joshcanhelp/auth0-wp-test.git ./wp-content/themes/auth0-wp-test
# Optional, clones the testing theme to assist with development
```

The main requirement for testing, though, is using the latest version of WordPress with our minimum supported PHP version. 


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

If you find a bug or if you have a feature request, please report them in the [Issues tab](https://github.com/auth0/wp-auth0/issues). Please do not report security vulnerabilities in a public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## Author

* [Auth0](https://auth0.com)

## License

This project is licensed under the MIT license. See the [LICENSE](LICENSE) file for more info.