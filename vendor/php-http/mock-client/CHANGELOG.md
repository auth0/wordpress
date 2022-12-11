# Change Log

## 1.5.0 - 2021-08-25

### Changed

- Provide `psr/http-client-implementation`
- Drop support for `php-http/httplug: 1.*` to be sure to implement a version of the client interface that implements the PSR.

## 1.4.1 - 2020-07-14

### Fixed

- Support PHP 7.4 and 8.0

## 1.4.0 - 2020-07-02

### Added

- Support for the PSR-17 response factory

### Changed

- Drop support for PHP 5 and 7.0
- Consitent implementation of union type checking

### Fixed

- `reset()` should not trigger `setDefaultException` error condition

## 1.3.1 - 2019-11-06

### Fixed

- `reset()` also resets `conditionalResults`

## 1.3.0 - 2019-02-21

### Added

- Conditional mock functionality

## 1.2.0 - 2019-01-19

### Added

- Support for HTTPlug 2.0.
- Support for php-http/client-common 2.0.

## 1.1.0 - 2018-01-08

### Added

- Default response functionality
- Default exception functionality
- `getLastRequest` method


## 1.0.1 - 2017-05-02

### Fixed

- `php-http/client-common` minimum dependency


## 1.0.0 - 2017-03-10

Stable release with no changes since 0.3


## 0.3.0 - 2016-02-26

### Added

- Support for custom MessageFactory

### Changed

- Updated dependencies


## 0.2.0 - 2016-02-01

### Changed

- Updated dependencies


## 0.1.1 - 2015-12-31


## 0.1.0 - 2015-12-29

### Added

- Initial release
