#!/bin/sh

bash bin/wait-for.sh db:3306
bash bin/install-wp-tests.sh wordpress_test root '' db:3306 latest
composer test-ci
tail -f /dev/null