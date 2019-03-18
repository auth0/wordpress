<?php
/**
 * Contains Class TestOptionCustomCssJs.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class TestOptionCustomCssJs.
 * Tests that custom JS and CSS settings fields are displayed properly.
 */
class TestOptionCustomCssJs extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * WP_Auth0_Admin_Appearance instance.
	 *
	 * @var WP_Auth0_Admin_Appearance
	 */
	public static $admin;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$admin = new WP_Auth0_Admin_Appearance( self::$opts );
	}

	/**
	 * Test that a textarea is present and proper documentation appears when the CSS field is populated.
	 */
	public function testThatTextareaIsPresentIfCssExists() {

		$field_args = [
			'label_for' => 'wpa0_custom_css',
			'opt_name'  => 'custom_css',
		];

		self::$opts->set( $field_args['opt_name'], '__test_css_input__' );

		ob_start();
		self::$admin->render_custom_css( $field_args );
		$field_html = ob_get_clean();

		// Check for correct documentation.
		$this->assertContains( 'This field is deprecated and will be removed', $field_html );
		$this->assertContains( 'Valid CSS to customize the Auth0 login form', $field_html );

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'textarea' );

		// Should have exactly one textarea field.
		$this->assertEquals( 1, $input->length );

		// Check for correct value set.
		$this->assertEquals( '__test_css_input__', $input->item( 0 )->nodeValue );

		// Textarea should have the correct id attribute.
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );

		// Textarea should have the correct name attribute.
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);
	}

	/**
	 * Test that a textarea is not present and proper documentation appears when the CSS field is empty.
	 */
	public function testThatTextareaIsNotPresentIfCssEmpty() {

		$field_args = [
			'label_for' => 'wpa0_custom_css',
			'opt_name'  => 'custom_css',
		];

		self::$opts->set( $field_args['opt_name'], '' );

		ob_start();
		self::$admin->render_custom_css( $field_args );
		$field_html = ob_get_clean();

		// Check for correct documentation.
		$this->assertContains( 'Custom styles should be loaded in an external file', $field_html );
		$this->assertContains( 'https://auth0.com/docs/cms/wordpress/troubleshoot', $field_html );

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'textarea' );

		// Should have no textarea field.
		$this->assertEmpty( $input->length );
	}

	/**
	 * Test that a textarea is present and proper documentation appears when the JS field is populated.
	 */
	public function testThatTextareaIsPresentIfJsExists() {

		$field_args = [
			'label_for' => 'wpa0_custom_js',
			'opt_name'  => 'custom_js',
		];

		self::$opts->set( $field_args['opt_name'], '__test_js_input__' );

		ob_start();
		self::$admin->render_custom_js( $field_args );
		$field_html = ob_get_clean();

		// Check for correct documentation.
		$this->assertContains( 'This field is deprecated and will be removed', $field_html );
		$this->assertContains( 'Valid JS to customize the Auth0 login form', $field_html );

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'textarea' );

		// Should have exactly one textarea field.
		$this->assertEquals( 1, $input->length );

		// Check for correct value set.
		$this->assertEquals( '__test_js_input__', $input->item( 0 )->nodeValue );

		// Textarea should have the correct id attribute.
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );

		// Textarea should have the correct name attribute.
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);
	}

	/**
	 * Test that a textarea is not present and proper documentation appears when the JS field is empty.
	 */
	public function testThatTextareaIsNotPresentIfJsEmpty() {

		$field_args = [
			'label_for' => 'wpa0_custom_js',
			'opt_name'  => 'custom_js',
		];

		self::$opts->set( $field_args['opt_name'], '' );

		ob_start();
		self::$admin->render_custom_js( $field_args );
		$field_html = ob_get_clean();

		// Check for correct documentation.
		$this->assertContains( 'Custom JavaScript should be loaded in an external file', $field_html );
		$this->assertContains( 'https://auth0.com/docs/cms/wordpress/troubleshoot', $field_html );

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'textarea' );

		// Should have no textarea field.
		$this->assertEmpty( $input->length );
	}
}
