<?php
use PHPUnit\Framework\TestCase;

/**
 * Class testWPAuth0Options
 */
class testWPAuth0Options extends TestCase {

	const FILTER_TEST_STRING = '__filter_test__';

	public function setUp() {
		global $wpdb;
		$wpdb->suppress_errors = false;
		$wpdb->show_errors     = true;
		$wpdb->db_connect();
		ini_set( 'display_errors', 1 );
	}

	/**
	 * Test the basic options functionality.
	 */
	public function testDefaultOptionsBehavior() {
		$opts = new WP_Auth0_Options();

		// Make sure the settings name did not change.
		$this->assertEquals( 'wp_auth0_settings', $opts->get_options_name() );

		// Make sure the number of options has not changed unintentionally.
		$this->assertEquals( 66, count( $opts->get_options() ) );
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
	 * Test the wp_auth0_get_option filter.
	 */
	public function testThatFiltersOverrideValues() {
		$opts = new WP_Auth0_Options();

		add_filter(
			'wp_auth0_get_option', function( $value, $key ) {
				return $key . self::FILTER_TEST_STRING;
			}, 10, 2
		);

		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$this->assertEquals( $opt_name . self::FILTER_TEST_STRING, $opts->get( $opt_name ) );
		}
	}

	/**
	 * Test that options can be set in memory and in the DB.
	 */
	public function testSet() {
		$opt_name       = 'domain';
		$expected_val_1 = rand();
		$expected_val_2 = rand();
		$opts           = new WP_Auth0_Options();

		// Test that a basic set without DB update works.
		$opts->set( $opt_name, $expected_val_1, false );
		$this->assertEquals( $expected_val_1, $opts->get( $opt_name ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertNotEquals( $expected_val_1, $db_options[ $opt_name ] );

		// Test that a basic set with DB update works.
		$opts->set( $opt_name, $expected_val_2, true );
		$this->assertEquals( $expected_val_2, $opts->get( $opt_name ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertEquals( $expected_val_2, $db_options[ $opt_name ] );
	}
}
