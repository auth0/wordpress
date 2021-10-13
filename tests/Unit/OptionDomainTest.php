<?php
/**
 * Contains Class TestOptionDomain.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

class OptionDomainTest extends WP_Auth0_Test_Case {

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
			'label_for' => 'wpa0_domain',
			'opt_name'  => 'domain',
		];
	}

	public function testThatDomainFieldRendersProperly() {
		ob_start();
		self::$admin->render_domain( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );

		$this->assertEquals( 1, $input->length );
		$this->assertEquals( 'wpa0_domain', $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );
		$this->assertEquals( 'your-tenant.auth0.com', $input->item( 0 )->getAttribute( 'placeholder' ) );
		$this->assertEquals( self::OPTIONS_NAME . '[domain]', $input->item( 0 )->getAttribute( 'name' ) );
		$this->assertEquals( 'border: 1px solid red;', $input->item( 0 )->getAttribute( 'style' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'value' ) );
	}

	public function testThatDomainFieldDoesNotShowErrorStylesIfValueIsPresent() {
		self::$opts->set( 'domain', '__test_domain__' );

		ob_start();
		self::$admin->render_domain( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'style' ) );
		$this->assertEquals( '__test_domain__', $input->item( 0 )->getAttribute( 'value' ) );
	}
}
