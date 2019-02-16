<?php
/**
 * Contains Class TestOptionSsoSlo.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class TestOptionSsoSlo.
 * Tests that Features > SSO and SLO function properly.
 */
class TestOptionSsoSlo extends WP_Auth0_Test_Case {

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
	public function testSsoFieldOutput() {
		$field_args = [
			'label_for' => 'wpa0_sso',
			'opt_name'  => 'sso',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_sso( $field_args );
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

		// Input should reference SLO field.
		$this->assertEquals( 'wpa0_singlelogout', $input->item( 0 )->getAttribute( 'data-expand' ) );

		// Check that saving a custom domain appears in the field value.
		self::$opts->set( $field_args['opt_name'], 1 );
		$this->assertEquals( 1, self::$opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		self::$admin->render_sso( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->item( 0 )->getAttribute( 'value' ) );
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

		// Get the field HTML.
		ob_start();
		self::$admin->render_sso( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->item( 0 )->getAttribute( 'value' ) );
	}

	public function testThatSsoIsValidatedOnSave() {
		$validated = self::$admin->basic_validation( [], [ 'sso' => false ] );
		$this->assertEquals( 0, $validated['sso'] );

		$validated = self::$admin->basic_validation( [], [ 'sso' => 0 ] );
		$this->assertEquals( 0, $validated['sso'] );

		$validated = self::$admin->basic_validation( [], [ 'sso' => 1 ] );
		$this->assertEquals( 1, $validated['sso'] );

		$validated = self::$admin->basic_validation( [], [ 'sso' => uniqid() ] );
		$this->assertEquals( 1, $validated['sso'] );
	}

	public function testThatSloIsValidatedOnSave() {
		$validated = self::$admin->basic_validation(
			[],
			[
				'sso'          => 1,
				'singlelogout' => false,
			]
		);
		$this->assertEquals( 0, $validated['singlelogout'] );

		$validated = self::$admin->basic_validation(
			[],
			[
				'sso'          => 1,
				'singlelogout' => 0,
			]
		);
		$this->assertEquals( 0, $validated['singlelogout'] );

		$validated = self::$admin->basic_validation(
			[],
			[
				'sso'          => 1,
				'singlelogout' => 1,
			]
		);
		$this->assertEquals( 1, $validated['singlelogout'] );

		$validated = self::$admin->basic_validation(
			[],
			[
				'sso'          => 1,
				'singlelogout' => uniqid(),
			]
		);
		$this->assertEquals( 1, $validated['singlelogout'] );
	}

	public function testThatSloIsTurnedOffIfSsoIsOff() {
		$validated = self::$admin->basic_validation(
			[],
			[
				'sso'          => 0,
				'singlelogout' => 1,
			]
		);
		$this->assertEquals( 0, $validated['singlelogout'] );
	}
}
