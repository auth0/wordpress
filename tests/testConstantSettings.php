<?php
use PHPUnit\Framework\TestCase;

/**
 * Class TestLockOptions.
 * Tests that Lock options output expected values based on given conditions.
 */
class TestConstantSettings extends TestCase {

	use setUpTestDb;

	const FILTER_TEST_STRING = '__filter_test__';

	const DEFAULT_CONSTANT_PREFIX = 'AUTH0_ENV_';

	/**
	 * Test that setting a constant will store the constant key.
	 */
	public function testThatConstructorStoresConstants() {
		// Set a few constant overrides.
		define( self::DEFAULT_CONSTANT_PREFIX . 'DOMAIN', rand() );
		define( self::DEFAULT_CONSTANT_PREFIX . 'CLIENT_ID', rand() );
		define( self::DEFAULT_CONSTANT_PREFIX . 'CLIENT_SECRET', rand() );

		// Make sure we have the right number of overrides.
		$opts = new WP_Auth0_Options();
		$this->assertCount( 3, $opts->get_all_constant_keys() );
	}

	/**
	 * Test that setting a constant will store the constant key.
	 */
	public function testConstantPrefixFilter() {
		$opts     = new WP_Auth0_Options();
		$opt_name = 'domain';

		$this->assertEquals( self::DEFAULT_CONSTANT_PREFIX . 'DOMAIN', $opts->get_constant_name( $opt_name ) );

		add_filter(
			'wp_auth0_settings_constant_prefix', function( $prefix ) {
				return '__TEST_PREFIX_' . $prefix;
			}, 10, 2
		);

		$this->assertEquals(
			'__TEST_PREFIX_' . self::DEFAULT_CONSTANT_PREFIX . 'DOMAIN',
			$opts->get_constant_name( $opt_name )
		);
	}

	/**
	 * Test that setting a constant will change it's value on output.
	 */
	public function testThatConstantOverridesWork() {
		$opts          = new WP_Auth0_Options();
		$expected_opts = [];
		$option_keys   = array_keys( $opts->get_options() );

		// Connections contains a sub-array of connection settings, does not need to be overridden.
		foreach ( $option_keys as $opt_name ) {
			$expected_opts[ $opt_name ] = rand();
			$constant_name              = $opts->get_constant_name( $opt_name );
			$this->assertNull( $opts->get_constant_val( $opt_name ) );
			define( $constant_name, $expected_opts[ $opt_name ] );
			$this->assertTrue( $opts->has_constant_val( $opt_name ) );
		}

		// Create a new instance of the class to reset constant-set options.
		$opts = new WP_Auth0_Options();
		foreach ( $option_keys as $opt_name ) {
			$this->assertEquals( $expected_opts[ $opt_name ], $opts->get_constant_val( $opt_name ), 'Opt name: ' . $opt_name );
			$this->assertEquals( $expected_opts[ $opt_name ], $opts->get( $opt_name ), 'Opt name: ' . $opt_name );
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
		$result = $opts->set( $opt_name, $expected_val_1, false );
		$this->assertTrue( $result );
		$this->assertEquals( $expected_val_1, $opts->get( $opt_name ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertNotEquals( $expected_val_1, $db_options[ $opt_name ] );

		// Test that a basic set with DB update works.
		$result = $opts->set( $opt_name, $expected_val_2, true );
		$this->assertTrue( $result );
		$this->assertEquals( $expected_val_2, $opts->get( $opt_name ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertEquals( $expected_val_2, $db_options[ $opt_name ] );
	}
	/**
	 * Test that options cannot be set when a constant is present.
	 */
	public function testSetWithConstant() {
		$opt_name      = 'domain';
		$expected_val  = rand();
		$opts          = new WP_Auth0_Options();
		$constant_name = $opts->get_constant_name( $opt_name );

		// Set a constant and make sure it works.
		$this->assertNull( $opts->get_constant_val( $opt_name ) );
		define( $constant_name, $expected_val );
		$this->assertEquals( $expected_val, $opts->get_constant_val( $opt_name ) );

		// Try to set an option with the constant set.
		$result = $opts->set( $opt_name, rand(), false );
		$this->assertFalse( $result );
		$this->assertNotEquals( $expected_val, $opts->get( $opt_name ) );
	}
}
