
## Support PHP 8+ (8.0, 8.1, 8.2 this November) and Modernize the Codebase
Today: Much of the codebase is written for PHP 5 in mind, and doesn't take advantage of the new PHP 7 features, including speed, memory efficiency, and scalability.

Next: Leverage new language features in PHP 7.4+ and WordPress 6 to improve the plugin's functionality and developer experience.

Complexity: L

## Incorporate Auth0-PHP v8
Today: The codebase relies on a third-party library, `lcobucci/jwt`. This is the same library used by previous versions of our other PHP libraties. We have progressively removed this dependency as the author has been difficult to contact and unaccomodating with backporting fixes to maintain compatibility. As a result, it is impossible for us to support PHP 8 without incorporating a new library.

Next: Rather than reinventing the wheel for every feature, we can simply build upon the strength of our existing Auth0-PHP library to provide a more modern and secure way to make Auth0 API requests. This would be the last step in migrating our PHP ecosystem from depending on third party libraries to using our own in-house code.

Complexity: M

## Simplify the Experience
Today: The WordPress plugin has grown into something akin to an SDK, where we provide extensibility points for developers to extend the functionality of the plugin, and to add new features to the plugin. This has lead to a very complex and spraling API that is hard to maintain or adequately support.

Next: We can simplify the experience of the plugin by providing a simpler API that is easier to use and more flexible. We can also provide a more intuitive and intuitive experience for developers. My goal is to strip the plugin to it's bare essentials, and provide a login experience that is easy to setup, configure and maintain over the long term for developers.

Complexity: M

## Support WordPress 6
Today: The current codebase was built with WordPress 4 in mind, and as a result has a great deal of legacy code that is not compatible and/or used in WordPress 6 ("dead code".)

Next: The only supported version of WordPress by Automattic is the latest release, and this remains true for WordPress 6. Users can easily auto-upgrade to WordPress 6 within their dashboard, so long as they're using a supported version of PHP (7.4+).

Complexity: L

## Improve Dependency Management
Today: We perform a complicate procedure to manage and integrate our dependencies, which also risks creating conflicts with other plugins' dependencies if the same package is relied up but uses a different version (even a patch/bugfix release.) This conflict will throw an error and cause either that plugin or ours to irevocably fail unless one of the plugins is disabled, but troubleshooting and figuring out which plugins are at fault is a difficult task.

Next: We can simplify this dependency management in three ways: (1) reduce our dependencies, but using the Auth0-PHP SDK directly, (2) use Composer's standard autoloader to load our dependencies, and (3) configure our autoloader to automatically prefix the incorporated dependencies within our package namespace, so that they can co-exist with other plugins' potentially conflicting dependencies without error.

Complexity: M

## Improve Test Suite Reliability and Performance
Today: We perform a complicated and time consuming procedure to test our codebase that incorporates the downloading, installation, configuration and running of a real WordPress instance, and as a result, our test suite is very slow and complex.

Next: It is unnecessary to test our codebase against a real WordPress instance. We can simplify this by moving our test suite to a similiar structure as our vanilla PHP and Laravel codebases, which incorporates mocking the standard API classes and responses of WordPress. As WordPress itself rarely introduces breaking changes, we can move these more complex tests to a seperate end-to-end test suite that runs periodically rather than on every pull request. Our target is 100% test coverage, the same as Auth0-PHP SDK v8.

Complexity: M
