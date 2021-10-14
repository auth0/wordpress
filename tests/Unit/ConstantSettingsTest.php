<?php
/**
 * Contains Class TestConstantSettings.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

class ConstantSettingsTest extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * Default constant setting prefix.
	 *
	 * @see WP_Auth0_Options::get_constant_name()
	 */
	const CONSTANT_PREFIX = 'AUTH0_ENV_';

	/**
	 * Test that setting a constant will store the constant key with a custom prefix.
	 */
	public function testThatCustomConstantPrefixIsUsed() {
		$opts     = new WP_Auth0_Options();
		$opt_name = 'domain';

		$this->assertEquals( self::CONSTANT_PREFIX . 'DOMAIN', $opts->get_constant_name( $opt_name ) );

		add_filter(
			'auth0_settings_constant_prefix',
			function( $prefix ) {
				return '__TEST_PREFIX_' . $prefix;
			},
			10,
			2
		);

		$this->assertEquals(
			'__TEST_PREFIX_' . self::CONSTANT_PREFIX . 'DOMAIN',
			$opts->get_constant_name( $opt_name )
		);
	}

	/**
	 * Test that no constants are set by default on load.
	 */
	public function testThatNotConstantsSetByDefault() {
		$opts = new WP_Auth0_Options();
		foreach ( array_keys( $opts->get_options() ) as $opt_name ) {
			$this->assertNull( $opts->get_constant_val( $opt_name ) );
		}
	}

	/**
	 * Test that setting a constant will change it's value on retrieval.
	 *
	 * @runInSeparateProcess
	 */
	public function testThatConstantValuesAreUsed() {
		define( self::CONSTANT_PREFIX . 'DOMAIN', '__test_domain__' );
		define( self::CONSTANT_PREFIX . 'CLIENT_ID', '__test_client_id__' );
		define( self::CONSTANT_PREFIX . 'CLIENT_SECRET', '__test_client_secret__' );

		$opts          = new WP_Auth0_Options();
		$constant_keys = $opts->get_all_constant_keys();

		$this->assertTrue( $opts->has_constant_val( 'domain' ) );
		$this->assertEquals( '__test_domain__', $opts->get( 'domain' ) );
		$this->assertContains( 'domain', $constant_keys );

		$this->assertTrue( $opts->has_constant_val( 'client_id' ) );
		$this->assertEquals( '__test_client_id__', $opts->get( 'client_id' ) );
		$this->assertContains( 'client_id', $constant_keys );

		$this->assertTrue( $opts->has_constant_val( 'client_secret' ) );
		$this->assertEquals( '__test_client_secret__', $opts->get( 'client_secret' ) );
		$this->assertContains( 'client_secret', $constant_keys );
	}

	/**
	 * Test that options cannot be set when a constant is present.
	 *
	 * @runInSeparateProcess
	 */
	public function testSetWithConstant() {
		$constant_val = rand();

		// Set a constant and make sure it works.
		define( self::CONSTANT_PREFIX . 'DOMAIN', $constant_val );
		$opts = new WP_Auth0_Options();
		$this->assertEquals( $constant_val, $opts->get( 'domain' ) );

		// Try to set an option with the constant set.
		$new_value  = str_shuffle( $constant_val );
		$set_result = $opts->set( 'domain', $new_value );
		$this->assertFalse( $set_result );
		$this->assertEquals( $constant_val, $opts->get( 'domain' ) );

		// Test that the constant value remains if a new option is set.
		$opts->set( 'client_id', $new_value );
		$this->assertEquals( $constant_val, $opts->get( 'domain' ) );
	}

	/**
	 * Test that constant settings will show a notice.
	 *
	 * @runInSeparateProcess
	 */
	public function testConstantSettingNoticeBasic() {

		$fields = [
			[
				'opt_name'        => 'domain',
				'label_for'       => 'wpa0_domain',
				'render_function' => 'render_domain',
				'value'           => rand(),
			],
			[
				'opt_name'        => 'client_id',
				'label_for'       => 'wpa0_client_id',
				'render_function' => 'render_client_id',
				'value'           => rand(),
			],
			[
				'opt_name'        => 'client_secret',
				'label_for'       => 'wpa0_client_secret',
				'render_function' => 'render_client_secret',
				'value'           => rand(),
			],
		];

		// Set all constant values before initializing the options class.
		foreach ( $fields as $field ) {
			$constant_name = self::CONSTANT_PREFIX . strtoupper( $field['opt_name'] );
			define( $constant_name, $field['value'] );
		}

		$opts  = new WP_Auth0_Options();
		$admin = new WP_Auth0_Admin_Basic( $opts );

		foreach ( $fields as $field ) {
			$constant_name = $opts->get_constant_name( $field['opt_name'] );
			ob_start();
			$admin->{$field['render_function']}( $field );
			$field_html = ob_get_clean();

			$input = $this->getDomListFromTagName( $field_html, 'input' );
			$this->assertTrue( $input->item( 0 )->hasAttribute( 'disabled' ) );
			$this->assertContains( 'Value is set in the constant', $field_html );
			$this->assertContains( $constant_name, $field_html );

			// Sensitive fields will not output the current value.
			$expected_value = 'client_secret' === $field['opt_name'] ? '[REDACTED]' : $field['value'];
			$this->assertContains( 'value="' . $expected_value . '"', $field_html );
		}
	}
}
