<?php
use PHPUnit\Framework\TestCase;

/**
 * Class TestAdvancedOptionsValidation.
 * Tests that Advanced settings are validated properly.
 */
class TestAdvancedOptionsValidation extends TestCase {

	use setUpTestDb;

	/**
	 * Test validation for the login redirection URL.
	 */
	public function testLoginRedirectValidation() {
		$opts            = new WP_Auth0_Options();
		$router          = new WP_Auth0_Routes( $opts );
		$admin           = new WP_Auth0_Admin_Advanced( $opts, $router );
		$home_url        = home_url();
		$home_url_host   = wp_parse_url( $home_url )['host'];
		$home_url_scheme = wp_parse_url( $home_url )['scheme'];
		$invalid_url     = 'https://auth0.com';

		// Test that no validation happens if the new value is the same as the old value.
		$input       = [ 'default_login_redirection' => $invalid_url ];
		$old_input   = [ 'default_login_redirection' => $invalid_url ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $invalid_url, $valid_input['default_login_redirection'] );

		// Test that the default is set when the input is empty.
		$input       = [ 'default_login_redirection' => '' ];
		$old_input   = [ 'default_login_redirection' => $home_url . '/path' ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $home_url, $valid_input['default_login_redirection'] );

		// Test that the defaults is set if URL is another site.
		$input       = [ 'default_login_redirection' => $invalid_url ];
		$old_input   = [ 'default_login_redirection' => $home_url . '/path' ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $old_input['default_login_redirection'], $valid_input['default_login_redirection'] );
		$old_input   = [ 'default_login_redirection' => '' ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $home_url, $valid_input['default_login_redirection'] );

		// Test that a URL with the same host as home_url will be saved.
		$input       = [ 'default_login_redirection' => $home_url . '/path' ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );

		// Test that a URL with a different scheme will be saved.
		$test_scheme = 'http' === $home_url_scheme ? 'https' : 'http';
		$input       = [ 'default_login_redirection' => $test_scheme . '://' . $home_url_host . '/path' ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );

		// Test that a subdomain of a main site can be used.
		$input       = [ 'default_login_redirection' => $home_url_scheme . '://www.auth0.' . $home_url_host ];
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );

		// Test that a main site of a subdomain can be used.
		$input = [ 'default_login_redirection' => $home_url ];
		update_option( 'home_url', $home_url_scheme . '://www.auth0.' . $home_url_host );
		$valid_input = $admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
		update_option( 'home_url', $home_url );
	}
}
