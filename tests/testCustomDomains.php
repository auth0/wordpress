<?php
/**
 * Contains Class TestCustomDomains.
 *
 * @package WP-Auth0
 * @since 3.7.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestCustomDomains.
 * Test custom domain functionality.
 */
class TestCustomDomains extends TestCase {

	use setUpTestDb;

	use domDocumentHelpers;

	/**
	 * Test the input HTML for the custom domain setting.
	 */
	public function testCustomDomainsFieldOutput() {
		$field_args  = [
			'label_for' => 'wpa0_custom_domain',
			'opt_name'  => 'custom_domain',
		];
		$opts        = new WP_Auth0_Options();
		$admin_basic = new WP_Auth0_Admin_Basic( $opts );

		// Get the field HTML.
		ob_start();
		$admin_basic->render_custom_domain( $field_args );
		$field_html = ob_get_clean();

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->length );
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );
		$this->assertEquals(
			testWPAuth0Options::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );

		// Check that saving a custom domain appears in the field value.
		$expected_value = 'example.org';
		$opts->set( $field_args['opt_name'], $expected_value );
		$this->assertEquals( $expected_value, $opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		$admin_basic->render_custom_domain( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( $expected_value, $input->item( 0 )->getAttribute( 'value' ) );
	}

	/**
	 * Test that a custom domain is output for the auth domain setting.
	 */
	public function testAuthDomain() {
		$auth0_domain  = 'test.auth0.com';
		$custom_domain = 'login.example.com';
		$opts          = WP_Auth0_Options::Instance();

		$opts->set( 'domain', $auth0_domain );
		$this->assertEquals( $auth0_domain, $opts->get_auth_domain() );
		$opts->set( 'custom_domain', $custom_domain );
		$this->assertEquals( $custom_domain, $opts->get_auth_domain() );
		$opts->set( 'custom_domain', '' );
		$this->assertEquals( $auth0_domain, $opts->get_auth_domain() );
	}
}
