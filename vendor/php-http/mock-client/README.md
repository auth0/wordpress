# Mock Client

[![Latest Version](https://img.shields.io/github/release/php-http/mock-client.svg?style=flat-square)](https://github.com/php-http/mock-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/php-http/mock-client.svg?style=flat-square)](https://travis-ci.org/php-http/mock-client)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-http/mock-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-http/mock-client)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-http/mock-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-http/mock-client)
[![Total Downloads](https://img.shields.io/packagist/dt/php-http/mock-client.svg?style=flat-square)](https://packagist.org/packages/php-http/mock-client)

**Mock HTTP client**


## Install

Via Composer

```bash
composer require --dev php-http/mock-client
```


## Usage

This client does not actually send requests to any server.

Instead it stores the request and returns a pre-set response or throws an exception. This client is useful for unit
testing code that depends on a HTTPlug client to send requests and receive responses.


## Documentation

Please see the [mock client](http://docs.php-http.org/en/latest/clients/mock-client.html) section in the [official documentation](http://docs.php-http.org).


## Testing

```bash
composer test
```


## Contributing

Please see our [contributing guide](http://docs.php-http.org/en/latest/development/contributing.html).


## Security

If you discover any security related issues, please contact us at [security@php-http.org](mailto:security@php-http.org).


## Credits

Thanks to [David de Boer](https://github.com/ddeboer) for implementing the mock client.


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
