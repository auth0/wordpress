![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Login by Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords + Passwordless. For all your WordPress instances. Powered by Auth0.

* [WordPress.org plugin page](https://wordpress.org/plugins/auth0/)
* [Documentation (installation, configuration, more)](https://auth0.com/docs/cms/wordpress)

## Installation

Please see the [Installation docs](https://auth0.com/docs/cms/wordpress/installation) for detailed instructions.

## API authentication

Please see the [JWT Authentication docs](https://auth0.com/docs/cms/wordpress/jwt-authentication) for more information. 

## Extending the plugin

Please see the [Extending docs](https://auth0.com/docs/cms/wordpress/extending) for information on existing hooks.

We're happy to review and approve new filters and actions that help you integrate even further in this plugin. Please see the **Contributing** section below for more information.

## Contributing

**Thank you in advance!**

* All PRs must use the `dev` branch as a base
* Change the least amount of code to achieve the goal; PRs with lots of whitespace changes and abstraction combined with a feature add or bug fix are difficult to review and may be rejected
* Use the latest version of WordPress and turn `WP_DEBUG` on
* If other plugins are installed during testing that might affect behavior, please list those in the PR description
* Run the [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) scripts listed below before creating your PR:
	* `composer phpcbf` to correct whitespace project-wide (required by CI)
	* `composer compat` to check for PHP version compatibility (currently 5.3; required by CI)
	* `composer phpcs-tests` to run code standard checks on test files (required by CI)
	* `composer phpcs-path ./path/to/file/changed.php` to find other potential issues (not required by CI currently)
	* `composer test` to run all tests (required by CI; see [Testing](#testing) below)

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

For a more complete script, [see this Gist](https://gist.github.com/joshcanhelp/50f66002643ece68f01bf5f94e1abe56).

The main requirement for testing, though, is using the latest version of WordPress with our minimum supported PHP version and testing both single and multi-site setups. 

### Testing

All new features and functionality should also include unit tests coverage. The plugin currently has the WP-CLI test scaffolding, a composer script, and tests for the latest functionality. 

WordPress testing is a bit different from typical testing in PHP, mainly because of the need for a working database. To run tests locally, you'll need to install the test suite, which is [covered here](https://make.wordpress.org/cli/handbook/plugin-unit-tests/#running-tests-locally). Run this install command:

```bash
bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

... changing the options for your local setup. Once that completes succesfully, you can run the Composer script:

```bash
composer test
```

... to run all tests or:

```bash
composer test-path ./tests/testFiletoRun.php
```

... to run a specific test. 

Please note that we are in the early stages of adding testing to this plugin so feedback on the current setup, including any problems you're having setting it up, are welcome and encouraged in the [Issues](https://github.com/auth0/wp-auth0/issues) tab. 


### CSS pre-processing

You need to install the stylus tool and run this command (from the plugin root):

```bash
stylus -c -o assets/css/initial-setup.css assets/css/initial-setup/main.styl
```

To watch and auto-compile it while working:

```bash
stylus -cw -o assets/css/initial-setup.css assets/css/initial-setup/main.styl
```

## Issue Reporting

If you find a bug or if you have a feature request, please report them in the [Issues tab](https://github.com/auth0/wp-auth0/issues). Please do not report security vulnerabilities in a public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## Author

* [Auth0](https://auth0.com)

## License

This project is licensed under the MIT license. See the [LICENSE](LICENSE) file for more info.