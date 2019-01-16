<?php
/**
 * Contains Class TestOptionLoginRedirect.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

/**
 * Class TestOptionLoginRedirect.
 * Tests that Advanced settings are validated properly.
 */
class TestOptionLoginRedirect extends WP_Auth0_Test_Case {

	/**
	 * WP_Auth0_Admin_Advanced instance.
	 *
	 * @var WP_Auth0_Admin_Advanced
	 */
	public static $admin;

	/**
	 * Empty input value.
	 *
	 * @var array
	 */
	public static $empty_input = [ 'default_login_redirection' => '' ];

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$router      = new WP_Auth0_Routes( self::$opts );
		self::$admin = new WP_Auth0_Admin_Advanced( self::$opts, $router );
	}

	/**
	 * Test that no validation happens if the new value is the same as the old value.
	 */
	public function testThatNoValidationRunsIfNoChange() {
		$invalid_url = 'https://auth0.com';
		$input       = [ 'default_login_redirection' => $invalid_url ];
		$old_input   = [ 'default_login_redirection' => $invalid_url ];

		$valid_input = self::$admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $invalid_url, $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that the default is set when the input is empty.
	 */
	public function testThatDefaultIsUsedIfNewValueIsEmpty() {
		$old_input = [ 'default_login_redirection' => home_url() . '/path' ];

		$valid_input = self::$admin->loginredirection_validation( $old_input, self::$empty_input );
		$this->assertEquals( home_url(), $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that the default is set if new URL is another site.
	 */
	public function testThatDefaultIsUsedIfNewUrlIsInvalid() {
		$invalid_url = 'https://auth0.com';
		$input       = [ 'default_login_redirection' => $invalid_url ];
		$old_input   = [ 'default_login_redirection' => home_url() . '/path' ];

		$valid_input = self::$admin->loginredirection_validation( $old_input, $input );
		$this->assertEquals( $old_input['default_login_redirection'], $valid_input['default_login_redirection'] );

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( home_url(), $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that a URL with the same host as home_url will be saved.
	 */
	public function testThatNewUrlWithSameHostIsValid() {
		$input = [ 'default_login_redirection' => home_url() . '/path' ];

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that a URL with a different scheme is valid.
	 */
	public function testThatNewUrlWithDifferentSchemeIsValid() {
		$input = [ 'default_login_redirection' => 'http://www.example.org' ];
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that a subdomain of a main site is valid.
	 */
	public function testThatSubdomainIsValid() {
		$input = [ 'default_login_redirection' => 'https://sub.sub.example.org' ];
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that a main site of a subdomain is valid.
	 */
	public function testThatParentDomainIsValid() {
		$input = [ 'default_login_redirection' => 'https://example.org' ];
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that sibling subdomains are valid.
	 */
	public function testThatSiblingSubdomainIsValid() {
		$input = [ 'default_login_redirection' => 'https://sub.example.org' ];
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
	}

	/**
	 * Test that other ports of the main site domain are valid.
	 */
	public function testThatDifferentPortIsValid() {
		$input = [ 'default_login_redirection' => 'http://www.example.org:8080' ];
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->loginredirection_validation( self::$empty_input, $input );
		$this->assertEquals( $input['default_login_redirection'], $valid_input['default_login_redirection'] );
	}
}
