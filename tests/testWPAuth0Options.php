<?php
/**
 * Contains Class TestWPAuth0Options.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

/**
 * Class TestWPAuth0Options
 */
class TestWPAuth0Options extends WP_Auth0_Test_Case {

	/**
	 * Test string to use.
	 */
	const FILTER_TEST_STRING = '__filter_test__';

	/**
	 * Total number of options.
	 */
	const DEFAULT_OPTIONS_COUNT = 44;

	/**
	 * Test the basic options functionality.
	 */
	public function testDefaultOptionsBehavior() {
		$opts = new WP_Auth0_Options();

		// Make sure the settings name did not change.
		$this->assertEquals( self::OPTIONS_NAME, $opts->get_options_name() );

		// Make sure the number of options has not changed unintentionally.
		$this->assertEquals( self::DEFAULT_OPTIONS_COUNT, count( $opts->get_options() ) );
		$this->assertEquals( self::DEFAULT_OPTIONS_COUNT, count( $opts->get_defaults() ) );
	}

	/**
	 * Test that our defaults are set properly.
	 */
	public function testThatDefaultsWork() {
		$opts = new WP_Auth0_Options();
		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$test_msg = 'Testing option: "' . $opt_name . '"';
			$this->assertEquals( $opts->get_default( $opt_name ), $opts->get( $opt_name ), $test_msg );
			$this->assertEquals( $opts->get_default( $opt_name ), wp_auth0_get_option( $opt_name ), $test_msg );
		}
	}

	/**
	 * Test that a missing option (considered an empty value) is not updated to a default "on" value.
	 */
	public function testThatGetOptionsDoesNotUseDefaultsWhenNotEmpty() {

		$db_options = get_option( self::OPTIONS_NAME );
		unset( $db_options['auto_login'] );
		unset( $db_options['singlelogout'] );
		update_option( self::OPTIONS_NAME, $db_options );

		$opts = new WP_Auth0_Options();

		$this->assertEmpty( $opts->get( 'auto_login' ) );
		$this->assertEmpty( $opts->get( 'singlelogout' ) );
	}

	/**
	 * Test the wp_auth0_get_option filter.
	 */
	public function testThatFiltersOverrideValues() {
		$opts = new WP_Auth0_Options();

		add_filter(
			// phpcs:ignore
			'wp_auth0_get_option', function( $value, $key ) {
				return $key . self::FILTER_TEST_STRING;
			},
			10,
			2
		);

		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$this->assertEquals( $opt_name . self::FILTER_TEST_STRING, $opts->get( $opt_name ) );
			$this->assertEquals( $opt_name . self::FILTER_TEST_STRING, wp_auth0_get_option( $opt_name ) );
		}
	}

	/**
	 * Test that options can be set in memory without a DB update
	 */
	public function testSetWithoutSave() {
		$opt_name     = 'domain';
		$expected_val = rand();
		$opts         = new WP_Auth0_Options();

		// Set the option and do not save.
		$opts->set( $opt_name, $expected_val, false );
		$this->assertEquals( $expected_val, $opts->get( $opt_name ) );

		// Get the DB-saved options and make sure it was not saved.
		$db_options = get_option( $opts->get_options_name() );
		$this->assertNotEquals( $expected_val, $db_options[ $opt_name ] );
	}

	/**
	 * Test that options can be set and saved to the DB.
	 */
	public function testSetWithSave() {
		$opt_name     = 'domain';
		$expected_val = rand();
		$opts         = new WP_Auth0_Options();

		// Set the option and flag to save (default).
		$opts->set( $opt_name, $expected_val );
		$this->assertEquals( $expected_val, $opts->get( $opt_name ) );

		// Make sure the saved value is correct.
		$db_options = get_option( $opts->get_options_name() );
		$this->assertEquals( $expected_val, $db_options[ $opt_name ] );
	}

	/**
	 * Test that update_all works.
	 */
	public function testUpdateAll() {
		$opt_name     = 'domain';
		$expected_val = rand();
		$opts         = new WP_Auth0_Options();

		// Set the option and flag to skip saving.
		$opts->set( $opt_name, $expected_val, false );
		$this->assertEquals( $expected_val, $opts->get( $opt_name ) );

		// Get the database option to make sure it was not saved.
		$db_options = get_option( $opts->get_options_name() );
		$this->assertNotEquals( $expected_val, $db_options[ $opt_name ] );

		// Explicitly save to the DB and make sure it's correct.
		$opts->update_all();
		$db_options = get_option( $opts->get_options_name() );
		$this->assertEquals( $expected_val, $db_options[ $opt_name ] );
	}

	/**
	 * Test that options can be saved for the first time.
	 */
	public function testDeleteAndSave() {
		$opts = new WP_Auth0_Options();

		// Make sure we do not have options saved.
		delete_option( $opts->get_options_name() );
		$this->assertFalse( get_option( $opts->get_options_name() ) );

		// Save and make sure it has been saved.
		$opts->save();
		$db_options = get_option( $opts->get_options_name() );
		$this->assertCount( self::DEFAULT_OPTIONS_COUNT, $db_options );

		// Now delete again and make sure it's gone.
		$this->assertTrue( $opts->delete() );
		$this->assertFalse( get_option( $opts->get_options_name() ) );
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
	 * Test that the remove method will only update options in memory.
	 */
	public function testThatRemoveStaysInMemory() {
		$opts = new WP_Auth0_Options();
		$opts->set( 'domain', '__test_domain__' );
		$this->assertNotNull( $opts->get( 'domain' ) );

		$opts->remove( 'domain' );
		$this->assertNull( $opts->get( 'domain' ) );
		$db_options = get_option( $opts->get_options_name() );
		$this->assertEquals( '__test_domain__', $db_options['domain'] );
	}
}
