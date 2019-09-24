<?php
/**
 * Contains Class TestOptionSlo.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class TestOptionSlo.
 * Tests that Features > SLO functions properly.
 */
class TestOptionSlo extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * WP_Auth0_Admin_Features instance.
	 *
	 * @var WP_Auth0_Admin_Features
	 */
	public static $admin;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$admin = new WP_Auth0_Admin_Features( self::$opts );
	}

	/**
	 * Test the input HTML for the custom domain setting.
	 */
	public function testSloFieldOutput() {
		$field_args = [
			'label_for' => 'wpa0_singlelogout',
			'opt_name'  => 'singlelogout',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_singlelogout( $field_args );
		$field_html = ob_get_clean();

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'input' );

		// Should only have one input field.
		$this->assertEquals( 1, $input->length );

		// Input should have the correct id attribute.
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );

		// Input should have the correct name attribute.
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);

		// Input should be a checkbox.
		$this->assertEquals( 'checkbox', $input->item( 0 )->getAttribute( 'type' ) );

		// Check that saving a custom domain appears in the field value.
		self::$opts->set( $field_args['opt_name'], 1 );
		$this->assertEquals( 1, self::$opts->get( $field_args['opt_name'] ) );

	}

	/**
	 * Test that SSO is validated properly on save.
	 * SSO must be on for SLO to validate to anything except false.
	 * See testThatSloIsTurnedOffIfSsoIsOff for tests regarding that behavior.
	 */
	public function testThatSloIsValidatedOnSave() {
		$validated = self::$admin->basic_validation( [], [ 'singlelogout' => false ] );
		$this->assertEquals( 0, $validated['singlelogout'] );

		$validated = self::$admin->basic_validation( [], [ 'singlelogout' => 0 ] );
		$this->assertEquals( 0, $validated['singlelogout'] );

		$validated = self::$admin->basic_validation( [], [ 'singlelogout' => 1 ] );
		$this->assertEquals( 1, $validated['singlelogout'] );

		$validated = self::$admin->basic_validation( [], [ 'singlelogout' => uniqid() ] );
		$this->assertEquals( 1, $validated['singlelogout'] );
	}
}
