<?php
/**
 * Contains Class TestRequiredEmail.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestRequiredEmail.
 * Tests that required email settings function properly.
 */
class TestRequiredEmail extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * Instance of WP_Auth0_Admin_Advanced.
	 *
	 * @var WP_Auth0_Admin_Advanced
	 */
	public static $admin;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$router      = new WP_Auth0_Routes( self::$opts );
		self::$admin = new WP_Auth0_Admin_Advanced( self::$opts, $router );
	}

	/**
	 * Test the input HTML for the custom domain setting.
	 */
	public function testRequiredEmailFieldOutput() {
		$field_args = [
			'label_for' => 'wpa0_verified_email',
			'opt_name'  => 'requires_verified_email',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_verified_email( $field_args );
		$field_html = ob_get_clean();

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->length );
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);
		$this->assertEquals( 'checkbox', $input->item( 0 )->getAttribute( 'type' ) );

		// Check that saving a custom domain appears in the field value.
		$expected_value = 1;
		self::$opts->set( $field_args['opt_name'], $expected_value );
		$this->assertEquals( $expected_value, self::$opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		self::$admin->render_verified_email( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( $expected_value, $input->item( 0 )->getAttribute( 'value' ) );
	}

	/**
	 * Test that the required email field is properly validated.
	 */
	public function testRequiredEmailValidation() {
		$validated_opts = self::$admin->basic_validation( [ 'requires_verified_email' => '1' ] );
		$this->assertEquals( true, $validated_opts['requires_verified_email'] );

		$validated_opts = self::$admin->basic_validation( [ 'requires_verified_email' => 1 ] );
		$this->assertEquals( true, $validated_opts['requires_verified_email'] );

		$validated_opts = self::$admin->basic_validation( [ 'requires_verified_email' => true ] );
		$this->assertEquals( true, $validated_opts['requires_verified_email'] );

		$validated_opts = self::$admin->basic_validation( [] );
		$this->assertEquals( false, $validated_opts['requires_verified_email'] );

		$validated_opts = self::$admin->basic_validation( [ 'requires_verified_email' => 0 ] );
		$this->assertEquals( false, $validated_opts['requires_verified_email'] );

		$validated_opts = self::$admin->basic_validation( [ 'requires_verified_email' => '' ] );
		$this->assertEquals( false, $validated_opts['requires_verified_email'] );
	}

	/**
	 * Test the input HTML for the custom domain setting.
	 */
	public function testSkipStrategiesFieldOutput() {
		$field_args = [
			'label_for' => 'wpa0_skip_strategies',
			'opt_name'  => 'skip_strategies',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_skip_strategies( $field_args );
		$field_html = ob_get_clean();

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->length );
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );

		// Check that saving a custom domain appears in the field value.
		$expected_value = 'auth0,twitter';
		self::$opts->set( $field_args['opt_name'], $expected_value );
		$this->assertEquals( $expected_value, self::$opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		self::$admin->render_skip_strategies( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( $expected_value, $input->item( 0 )->getAttribute( 'value' ) );
	}

	/**
	 * Test that the skip required email field is properly validated.
	 */
	public function testSkipRequiredEmailValidation() {
		$opt_name = 'skip_strategies';

		$validated_opts = self::$admin->basic_validation( [ $opt_name => '' ] );
		$this->assertEquals( '', $validated_opts[ $opt_name ] );

		$validated_opts = self::$admin->basic_validation( [ $opt_name => '  ' ] );
		$this->assertEquals( '', $validated_opts[ $opt_name ] );

		$validated_opts = self::$admin->basic_validation( [ $opt_name => 'auth0' ] );
		$this->assertEquals( 'auth0', $validated_opts[ $opt_name ] );

		$validated_opts = self::$admin->basic_validation( [ $opt_name => ' auth0 ' ] );
		$this->assertEquals( 'auth0', $validated_opts[ $opt_name ] );

		$validated_opts = self::$admin->basic_validation( [ $opt_name => 'auth0,twitter' ] );
		$this->assertEquals( 'auth0,twitter', $validated_opts[ $opt_name ] );
	}

	/**
	 * Test that the skip required email field is properly used.
	 */
	public function testSkipRequiredEmailOption() {
		$opt_name = 'skip_strategies';

		$this->assertFalse( self::$opts->strategy_skips_verified_email( null ) );
		$this->assertFalse( self::$opts->strategy_skips_verified_email( '' ) );

		self::$opts->set( $opt_name, '' );
		$this->assertFalse( self::$opts->strategy_skips_verified_email( 'auth0' ) );
		$this->assertFalse( self::$opts->strategy_skips_verified_email( 'twitter' ) );

		self::$opts->set( $opt_name, 'auth0' );
		$this->assertTrue( self::$opts->strategy_skips_verified_email( 'auth0' ) );
		$this->assertFalse( self::$opts->strategy_skips_verified_email( 'twitter' ) );

		self::$opts->set( $opt_name, 'auth0,twitter' );
		$this->assertTrue( self::$opts->strategy_skips_verified_email( 'auth0' ) );
		$this->assertTrue( self::$opts->strategy_skips_verified_email( 'twitter' ) );
		$this->assertFalse( self::$opts->strategy_skips_verified_email( 'github' ) );

		self::$opts->set( $opt_name, ' auth0, github ' );
		$this->assertTrue( self::$opts->strategy_skips_verified_email( 'auth0' ) );
		$this->assertFalse( self::$opts->strategy_skips_verified_email( 'twitter' ) );
		$this->assertTrue( self::$opts->strategy_skips_verified_email( 'github' ) );
	}
}
