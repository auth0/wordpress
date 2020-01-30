<?php
/**
 * Contains Class TestSafeRedirects.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestSafeRedirects.
 */
class TestSafeRedirects extends WP_Auth0_Test_Case {

	use RedirectHelpers;

	public function testThatDomainIsAllowedToSafeRedirect() {
		$this->startRedirectHalting();
		self::auth0Ready();
		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'singlelogout', 1 );

		$login = new WP_Auth0_LoginManager( new WP_Auth0_UsersRepo( self::$opts ), self::$opts );

		try {
			// Calls wp_safe_redirect.
			$login->logout();
			$redirect_data = [ 'location' => 'Redirect not found' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertStringStartsWith( 'https://test.auth0.com', $redirect_data['location'] );
	}

	public function testThatCustomDomainIsAllowedToSafeRedirect() {
		$this->startRedirectHalting();
		self::auth0Ready();
		self::$opts->set( 'custom_domain', 'custom-test.auth0.com' );
		self::$opts->set( 'singlelogout', 1 );

		$login = new WP_Auth0_LoginManager( new WP_Auth0_UsersRepo( self::$opts ), self::$opts );

		try {
			// Calls wp_safe_redirect.
			$login->logout();
			$redirect_data = [ 'location' => 'Redirect not found' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertStringStartsWith( 'https://custom-test.auth0.com', $redirect_data['location'] );
	}

	public function testThatAuth0ServerDomainIsAllowedToSafeRedirect() {
		$this->startRedirectHalting();
		self::auth0Ready();
		self::$opts->set( 'auth0_server_domain', 'auth0-server-test.auth0.com' );

		$conn_profile = new WP_Auth0_InitialSetup_ConnectionProfile( self::$opts );

		try {
			// Calls wp_safe_redirect.
			$conn_profile->callback();
			$redirect_data = [ 'location' => 'Redirect not found' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertStringStartsWith( 'https://auth0-server-test.auth0.com', $redirect_data['location'] );
	}

	public function testThatDefaultAuth0ServerDomainIsAllowedToSafeRedirect() {
		$this->startRedirectHalting();
		self::auth0Ready();

		$conn_profile = new WP_Auth0_InitialSetup_ConnectionProfile( self::$opts );

		try {
			// Calls wp_safe_redirect.
			$conn_profile->callback();
			$redirect_data = [ 'location' => 'Redirect not found' ];
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertStringStartsWith( 'https://auth0.auth0.com', $redirect_data['location'] );
	}

}
