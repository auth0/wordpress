<?php
/**
 * Contains Class TestConstantSettings.
 *
 * @package WP-Auth0
 * @since 3.7.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestConstantSettings.
 * Tests that constant-defined settings work as expected.
 */
class TestConstantSettings extends TestCase {

	use setUpTestDb;

	use domDocumentHelpers;

	/**
	 * Test string to use.
	 */
	const FILTER_TEST_STRING = '__filter_test__';

	/**
	 * Default constant setting prefix.
	 *
	 * @see WP_Auth0_Options_Generic::get_constant_name()
	 */
	const DEFAULT_CONSTANT_PREFIX = 'AUTH0_ENV_';

	/**
	 * Notice text for a field with a constant value set.
	 *
	 * @see WP_Auth0_Admin_Generic::render_const_notice()
	 */
	const CONSTANT_NOTICE_TEXT = 'Value is set in the constant';

	/**
	 * Test that setting a constant will store the constant key.
	 *
	 * @runInSeparateProcess
	 */
	public function testThatConstructorStoresConstants() {
		// Set a few constant overrides.
		define( self::DEFAULT_CONSTANT_PREFIX . 'DOMAIN', rand() );
		define( self::DEFAULT_CONSTANT_PREFIX . 'CLIENT_ID', rand() );
		define( self::DEFAULT_CONSTANT_PREFIX . 'CLIENT_SECRET', rand() );

		// Make sure we have the right number of overrides.
		$opts          = new WP_Auth0_Options();
		$constant_keys = $opts->get_all_constant_keys();
		$this->assertCount( 3, $constant_keys );
		$this->assertContains( 'domain', $constant_keys );
		$this->assertContains( 'client_id', $constant_keys );
		$this->assertContains( 'client_secret', $constant_keys );
	}

	/**
	 * Test that setting a constant will store the constant key.
	 */
	public function testConstantPrefixFilter() {
		$opts     = new WP_Auth0_Options();
		$opt_name = 'domain';

		$this->assertEquals( self::DEFAULT_CONSTANT_PREFIX . 'DOMAIN', $opts->get_constant_name( $opt_name ) );

		add_filter(
			'auth0_settings_constant_prefix',
			function( $prefix ) {
				return '__TEST_PREFIX_' . $prefix;
			},
			10,
			2
		);

		$this->assertEquals(
			'__TEST_PREFIX_' . self::DEFAULT_CONSTANT_PREFIX . 'DOMAIN',
			$opts->get_constant_name( $opt_name )
		);
	}

	/**
	 * Test that setting a constant will change it's value on output.
	 *
	 * @runInSeparateProcess
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
	 *
	 * @runInSeparateProcess
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

	/**
	 * Test that commonly-overridden settings will show a notice.
	 *
	 * @runInSeparateProcess
	 */
	public function testConstantSettingNoticeBasic() {
		$opts  = new WP_Auth0_Options();
		$admin = new WP_Auth0_Admin_Basic( $opts );

		$fields = [
			[
				'opt_name'        => 'domain',
				'label_for'       => 'wpa0_domain',
				'render_function' => 'render_domain',
			],
			[
				'opt_name'        => 'client_id',
				'label_for'       => 'wpa0_client_id',
				'render_function' => 'render_client_id',
			],
			[
				'opt_name'        => 'client_secret',
				'label_for'       => 'wpa0_client_secret',
				'render_function' => 'render_client_secret',
			],
			[
				'opt_name'        => 'auth0_app_token',
				'label_for'       => 'wpa0_auth0_app_token',
				'render_function' => 'render_auth0_app_token',
			],
		];

		foreach ( $fields as $field ) {
			$constant_name = self::DEFAULT_CONSTANT_PREFIX . strtoupper( $field['opt_name'] );
			$override_val  = self::FILTER_TEST_STRING . rand();
			define( $constant_name, $override_val );

			ob_start();
			$admin->{$field['render_function']}( $field );
			$field_html = ob_get_clean();

			$input = $this->getDomListFromTagName( $field_html, 'input' );
			$this->assertTrue( $input->item( 0 )->hasAttribute( 'disabled' ) );
			$this->assertContains( self::CONSTANT_NOTICE_TEXT, $field_html );
			$this->assertContains( $constant_name, $field_html );
		}
	}
}
