<?php
use PHPUnit\Framework\TestCase;

/**
 * Class testWPAuth0Options
 */
class testWPAuth0Options extends TestCase
{
	const FILTER_TEST_STRING = '__filter_test__';

	const CONNECTION_SETTING_KEYS = [
		'social_twitter_key',
		'social_twitter_secret',
		'social_facebook_key',
		'social_facebook_secret',
	];

	public function setUp() {
		global $wpdb;
		$wpdb->suppress_errors = false;
		$wpdb->show_errors = true;
		$wpdb->db_connect(); ini_set('display_errors', 1 );
	}

	/**
	 * Test the basic options functionality.
	 */
	public function testDefaultOptionsBehavior() {
		$opts = new WP_Auth0_Options();

		// Make sure the settings name did not change.
		$this->assertEquals( 'wp_auth0_settings', $opts->get_options_name() );

		// Make sure the number of options has not changed unintentionally.
		$this->assertEquals( 67, count( $opts->get_options() ) );

		// Make sure there are no constant overrides.
		$this->assertEmpty( $opts->get_all_constant_keys() );

		// Test constant name building.
		$this->assertEquals( 'AUTH0_ENV_TEST_GET_CONSTANT_NAME', $opts->get_constant_name( 'Test_Get_Constant_Name' ) );
	}

	/**
	 * Test that our defaults are set properly.
	 */
	public function testThatDefaultsWork() {
		$opts = new WP_Auth0_Options();
		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$test_msg = 'Testing option: "' . $opt_name . '"';
			$this->assertEquals( $opts->get_default( $opt_name ), $opts->get( $opt_name ), $test_msg );
		}
	}

	/**
	 * Test that setting a constant will store the constant key.
	 */
	public function testThatConstructorStoresConstants() {
		// Set a few constant overrides.
		define( 'AUTH0_ENV_DOMAIN', rand() );
		define( 'AUTH0_ENV_CLIENT_ID', rand() );
		define( 'AUTH0_ENV_CLIENT_SECRET', rand() );

		// Make sure we have the right number of overrides.
		$opts = new WP_Auth0_Options();
		$this->assertEquals( 3, count( $opts->get_all_constant_keys() ) );
	}

	/**
	 * Test that setting a constant will change it's value on output.
	 */
	public function testThatConstantOverridesWork() {
		$opts = new WP_Auth0_Options();
		$expected_opts = [];
		$option_keys = array_keys( $opts->get_options() );

		// Connections contains a sub-array of connection settings, does not need to be overridden.
		unset( $option_keys[ array_search( 'connections', $option_keys ) ] );

		foreach ( $option_keys as $opt_name ) {
			$expected_opts[ $opt_name ] = rand();
			$constant_name = $opts->get_constant_name( $opt_name );
			$this->assertNull( $opts->get_constant_val( $opt_name ) );
			define( $constant_name, $expected_opts[ $opt_name ] );
			$this->assertTrue( $opts->has_constant_val( $opt_name ) );
		}

		// Create a new instance of the class to reset constant-set options.
		$opts = new WP_Auth0_Options();

		// Only check non-connection settings.
		$option_keys_regular = array_diff( $option_keys, self::CONNECTION_SETTING_KEYS );
		foreach ( $option_keys_regular as $opt_name ) {
			$this->assertEquals( $expected_opts[$opt_name], $opts->get_constant_val( $opt_name ), 'Opt name: ' . $opt_name );
			$this->assertEquals( $expected_opts[$opt_name], $opts->get( $opt_name ), 'Opt name: ' . $opt_name );
		}

		// Only check connection settings.
		$option_keys_connection = array_diff( $option_keys, $option_keys_regular );
		foreach ( $option_keys_connection as $opt_name ) {
			$this->assertEquals( $expected_opts[$opt_name], $opts->get_constant_val( $opt_name ), 'Opt name: ' . $opt_name );
			$this->assertEquals( $expected_opts[$opt_name], $opts->get_connection( $opt_name ), 'Opt name: ' . $opt_name );
		}
	}

	/**
	 * Test the wp_auth0_get_option filter.
	 */
	public function testThatFiltersOverrideValues () {
		$opts = new WP_Auth0_Options();

		add_filter( 'wp_auth0_get_option', function( $value, $key ) {
			return $key . self::FILTER_TEST_STRING;
		}, 10, 2 );

		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$this->assertEquals( $opt_name . self::FILTER_TEST_STRING, $opts->get( $opt_name ) );
		}
	}

	/**
	 * Test that options can be set in memory and in the DB.
	 */
	public function testSet() {
		$opt_name = 'domain';
		$expected_val_1 = rand();
		$expected_val_2 = rand();
		$opts = new WP_Auth0_Options();

		// Test that a basic set without DB update works.
		$result = $opts->set( $opt_name, $expected_val_1, FALSE );
		$this->assertTrue( $result );
		$this->assertEquals( $expected_val_1, $opts->get( $opt_name ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertNotEquals( $expected_val_1, $db_options[ $opt_name ] );

		// Test that a basic set with DB update works.
		$result = $opts->set( $opt_name, $expected_val_2, TRUE );
		$this->assertTrue( $result );
		$this->assertEquals( $expected_val_2, $opts->get( $opt_name ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertEquals( $expected_val_2, $db_options[ $opt_name ] );
	}

	/**
	 * Test that options cannot be set when a constant is present.
	 */
	public function testSetWithConstant() {
		$opt_name = 'domain';
		$expected_val = rand();
		$opts = new WP_Auth0_Options();
		$constant_name = $opts->get_constant_name( $opt_name );

		// Set a constant and make sure it works.
		$this->assertNull( $opts->get_constant_val( $opt_name ) );
		define( $constant_name, $expected_val );
		$this->assertEquals( $expected_val, $opts->get_constant_val( $opt_name ) );

		// Try to set an option with the constant set.
		$result = $opts->set( $opt_name, rand(), FALSE );
		$this->assertFalse( $result );
		$this->assertNotEquals( $expected_val, $opts->get( $opt_name ) );
	}

	public function testSetOptsConstantArrayVal() {
		$opt_name = 'domain';
		$opt_name_connection = 'social_twitter_key';
		$expected_val = rand();
		$opts = new WP_Auth0_Options();
		$constant_name = $opts->get_constant_name( $opt_name );
		$constant_name_connection = $opts->get_constant_name( $opt_name_connection );
		$opts_array = [];

		// Test that we get a null value when there is no constant set.
		$opts_array = $opts->set_opts_array_constant_val( $opts_array, $opt_name );
		$this->assertNull( $opts_array[ $opt_name ] );

		// Define a constant and test that we get the right value back.
		define( $constant_name, $expected_val );
		$opts_array = $opts->set_opts_array_constant_val( $opts_array, $opt_name );
		$this->assertEquals( $expected_val, $opts_array[ $opt_name ] );

		// Test the same for connection settings
		$opts_array = $opts->set_opts_array_constant_val( $opts_array, $opt_name_connection );
		$this->assertNull( $opts_array['connections'][ $opt_name_connection ] );
		define( $constant_name_connection, $expected_val );
		$opts_array = $opts->set_opts_array_constant_val( $opts_array, $opt_name_connection );
		$this->assertEquals( $expected_val, $opts_array['connections'][ $opt_name_connection ] );
	}
}