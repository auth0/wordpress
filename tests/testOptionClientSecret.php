<?php
/**
 * Contains Class TestOptionClientSecret.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

/**
 * Class TestOptionClientSecret.
 * Tests that Basic > Client Secret functions properly.
 */
class TestOptionClientSecret extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * WP_Auth0_Admin_Basic instance.
	 *
	 * @var WP_Auth0_Admin_Basic
	 */
	public static $admin;

	/**
	 * Domain settings field args
	 *
	 * @var array
	 */
	public static $field_args;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$admin      = new WP_Auth0_Admin_Basic( self::$opts );
		self::$field_args = [
			'label_for' => 'wpa0_client_secret',
			'opt_name'  => 'client_secret',
		];
	}

	public function testThatClientSecretFieldRendersProperly() {
		ob_start();
		self::$admin->render_client_secret( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );

		$this->assertEquals( 1, $input->length );
		$this->assertEquals( 'wpa0_client_secret', $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );
		$this->assertEquals( self::OPTIONS_NAME . '[client_secret]', $input->item( 0 )->getAttribute( 'name' ) );
		$this->assertEquals( 'border: 1px solid red;', $input->item( 0 )->getAttribute( 'style' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'value' ) );
	}

	public function testThatClientSecretFieldDoesNotShowErrorStylesIfValueIsPresent() {
		self::$opts->set( 'client_secret', '__test_client_secret__' );

		ob_start();
		self::$admin->render_client_secret( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'style' ) );
		$this->assertEquals( '[REDACTED]', $input->item( 0 )->getAttribute( 'value' ) );
	}
}
