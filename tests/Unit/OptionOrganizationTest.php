<?php
/**
 * Contains Class TestOptionOrganization.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

class OptionOrganizationTest extends WP_Auth0_Test_Case {

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
			'label_for' => 'wpa0_organization',
			'opt_name'  => 'organization',
		];
	}

	public function testThatOrganizationFieldRendersProperly() {
		ob_start();
		self::$admin->render_organization( self::$field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );

		$this->assertEquals( 1, $input->length );
		$this->assertEquals( 'wpa0_organization', $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );
		$this->assertEquals( self::OPTIONS_NAME . '[organization]', $input->item( 0 )->getAttribute( 'name' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'value' ) );
	}
}
