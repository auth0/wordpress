<?php
use PHPUnit\Framework\TestCase;

/**
 * Class testWPAuth0Options
 */
class testWPAuth0Options extends TestCase
{
	const FILTER_TEST_STRING = '__filter_test__';

	/**
	 * Test the basic options functionality.
	 */
	public function testDefaultOptionsBehavior()
	{
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
	 *
	 */
	public function testThatDefaultsWork() {
		$opts = new WP_Auth0_Options();
		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$test_msg = 'Testing option: "' . $opt_name . '"';
			$this->assertEquals( $opts->get_default( $opt_name ), $opts->get( $opt_name ), $test_msg );
		}
	}

	/**
	 *
	 */
	public function testThatConstructorStoresConstants()
	{
		// Set a few constant overrides.
		define( 'AUTH0_ENV_DOMAIN', rand() );
		define( 'AUTH0_ENV_CLIENT_ID', rand() );
		define( 'AUTH0_ENV_CLIENT_SECRET', rand() );

		// Make sure we have the right number of overrides.
		$opts = new WP_Auth0_Options();
		$this->assertEquals( 3, count( $opts->get_all_constant_keys() ) );
	}

	/**
	 *
	 */
	public function testThatConstantOverridesWork() {
		$opts = new WP_Auth0_Options();
		$expected_opts = [];

		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$expected_opts[ $opt_name ] = rand();
			$constant_name = $opts->get_constant_name( $opt_name );
			$this->assertNull( $opts->get_constant_val( $opt_name ) );
			define( $constant_name, $expected_opts[ $opt_name ] );
		}

		$opts = new WP_Auth0_Options();
		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$this->assertEquals( $expected_opts[$opt_name], $opts->get_constant_val( $opt_name ) );
			$this->assertEquals( $expected_opts[$opt_name], $opts->get( $opt_name ) );
		}
	}

	/**
	 * ≤
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
}