<?php
/**
 * Contains Class TestOptionLoginRedirect.
 *
 * @package WP-Auth0
 *
 * @since 3.7.0
 */

class OptionLoginRedirectTest extends WP_Auth0_Test_Case {

	/**
	 * WP_Auth0_Admin_Advanced instance.
	 *
	 * @var WP_Auth0_Admin_Advanced
	 */
	public static $admin;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$router      = new WP_Auth0_Routes( self::$opts );
		self::$admin = new WP_Auth0_Admin_Advanced( self::$opts, $router );
	}

	/**
	 * Test that no validation happens if the new value is the same as the old value.
	 */
	public function testThatNoValidationRunsIfNoChange() {
		$invalid_url = 'https://auth0.com';

		$valid_input = self::$admin->validate_login_redirect( $invalid_url, $invalid_url );
		$this->assertEquals( $invalid_url, $valid_input );
	}

	/**
	 * Test that the default is set when the input is empty.
	 */
	public function testThatDefaultIsUsedIfNewValueIsEmpty() {
		self::$opts->set( 'default_login_redirection', home_url() . '/path' );

		$valid_input = self::$admin->validate_login_redirect( '' );
		$this->assertEquals( home_url(), $valid_input );
	}

	/**
	 * Test that the default is set if new URL is another site.
	 */
	public function testThatDefaultIsUsedIfNewUrlIsInvalid() {
		self::$opts->set( 'default_login_redirection', home_url() . '/path' );

		$valid_input = self::$admin->validate_login_redirect( 'https://auth0.com' );
		$this->assertEquals( home_url() . '/path', $valid_input );
	}

	/**
	 * Test that the existing is used if new URL is another site.
	 */
	public function testThatDefaultIsUsedIfNewUrlIsInvalidAndNoSavedValue() {
		self::$opts->set( 'default_login_redirection', null );

		$valid_input = self::$admin->validate_login_redirect( 'https://auth0.com' );
		$this->assertEquals( home_url(), $valid_input );
	}

	/**
	 * Test that a URL with the same host as home_url will be saved.
	 */
	public function testThatNewUrlWithSameHostIsValid() {
		$valid_input = self::$admin->validate_login_redirect( home_url( 'path' ) );

		$this->assertEquals( home_url( 'path' ), $valid_input );
	}

	/**
	 * Test that a URL with a different scheme is valid.
	 */
	public function testThatNewUrlWithDifferentSchemeIsValid() {
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->validate_login_redirect( 'http://www.example.org' );
		$this->assertEquals( 'http://www.example.org', $valid_input );
	}

	/**
	 * Test that a subdomain of a main site is valid.
	 */
	public function testThatSubdomainIsValid() {
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->validate_login_redirect( 'https://sub.sub.example.org' );
		$this->assertEquals( 'https://sub.sub.example.org', $valid_input );
	}

	/**
	 * Test that a main site of a subdomain is valid.
	 */
	public function testThatParentDomainIsValid() {
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->validate_login_redirect( 'https://example.org' );
		$this->assertEquals( 'https://example.org', $valid_input );
	}

	/**
	 * Test that sibling subdomains are valid.
	 */
	public function testThatSiblingSubdomainIsValid() {
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->validate_login_redirect( 'https://sub.example.org' );
		$this->assertEquals( 'https://sub.example.org', $valid_input );
	}

	/**
	 * Test that other ports of the main site domain are valid.
	 */
	public function testThatDifferentPortIsValid() {
		update_option( 'home_url', 'https://www.example.org' );

		$valid_input = self::$admin->validate_login_redirect( 'http://www.example.org:8080' );
		$this->assertEquals( 'http://www.example.org:8080', $valid_input );
	}
}
