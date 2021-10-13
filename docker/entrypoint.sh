#!/bin/sh

composer validate

rm -rf composer.lock

composer install --no-interaction --prefer-dist

php ./vendor/bin/phpunit --testsuite unit --stop-on-failure --stop-on-error

php --version
