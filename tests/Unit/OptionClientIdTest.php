<?php
/**
 * Contains Class TestOptionClientId.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

class OptionClientIdTest extends WP_Auth0_Test_Case {

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
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$admin      = new WP_Auth0_Admin_Basic( self::$opts );
		self::$field_args = [
			'label_for' => 'wpa0_client_id',
			'opt_name'  => 'client_id',
		];
	}

	public function testThatClientIdFieldRendersProperly() {
		ob_start();
		self::$admin->render_client_id( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );

		$this->assertEquals( 1, $input->length );
		$this->assertEquals( 'wpa0_client_id', $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );
		$this->assertEquals( self::OPTIONS_NAME . '[client_id]', $input->item( 0 )->getAttribute( 'name' ) );
		$this->assertEquals( 'border: 1px solid red;', $input->item( 0 )->getAttribute( 'style' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'value' ) );
	}

	public function testThatClientIdFieldDoesNotShowErrorStylesIfValueIsPresent() {
		self::$opts->set( 'client_id', '__test_client_id__' );

		ob_start();
		self::$admin->render_client_id( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'style' ) );
		$this->assertEquals( '__test_client_id__', $input->item( 0 )->getAttribute( 'value' ) );
	}
}
