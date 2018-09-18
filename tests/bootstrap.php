<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WP-Auth0
 */

echo 'PHP version: ' . phpversion() . "\n";

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/WP_Auth0.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
if ( ! file_exists( $_tests_dir . '/includes/bootstrap.php' ) ) {
	echo "Could not find $_tests_dir/includes/bootstrap.php" . PHP_EOL;
	exit( 1 );
}

require $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/../vendor/autoload.php';

require dirname( __FILE__ ) . '/classes/Test_WP_Auth0_Api_Abstract.php';

require dirname( __FILE__ ) . '/traits/ajaxHelpers.php';
require dirname( __FILE__ ) . '/traits/domDocumentHelpers.php';
require dirname( __FILE__ ) . '/traits/hookHelpers.php';
require dirname( __FILE__ ) . '/traits/httpHelpers.php';
require dirname( __FILE__ ) . '/traits/setUpTestDb.php';
require dirname( __FILE__ ) . '/traits/usersHelper.php';
