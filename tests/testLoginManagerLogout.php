<?php
/**
 * Contains Class TestLoginManagerLogout.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestLoginManagerLogout.
 * Test the WP_Auth0_LoginManager::logout() method.
 */
class TestLoginManagerLogout extends WP_Auth0_Test_Case {

	use RedirectHelpers;

	/**
	 * WP_Auth0_LoginManager instance to test.
	 *
	 * @var WP_Auth0_LoginManager
	 */
	protected $login;

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();
		$this->login = new WP_Auth0_LoginManager( new WP_Auth0_UsersRepo( self::$opts ), self::$opts );
	}

	/**
	 * Test that logout does not redirect to Auth0 if the plugin is not ready (no domain or client ID).
	 * This is the default state before the tests run.
	 */
	public function testThatNothingHappensIfNotReady() {
		$this->startRedirectHalting();

		$this->assertNull( wp_auth0_process_logout() );
	}

	/**
	 * Test that logout does not redirect to Auth0 if SLO is not on.
	 */
	public function testThatNothingHappensIfNotSlo() {
		$this->startRedirectHalting();
		self::auth0Ready( true );
		self::$opts->set( 'singlelogout', 0 );
		self::$opts->set( 'auto_login', 0 );

		$this->assertNull( wp_auth0_process_logout() );
	}

	/**
	 * Test that a redirect to the Auth0 logout URL happens if SLO is turned on.
	 */
	public function testThatRedirectHappensIfSlo() {
		$this->startRedirectHalting();
		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'singlelogout', 1 );

		$redirect_data_slo = [];
		try {
			wp_auth0_process_logout();
		} catch ( Exception $e ) {
			$redirect_data_slo = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 302, $redirect_data_slo['status'] );
		$this->assertNotEmpty( $redirect_data_slo['location'] );

		$logout_url_slo = parse_url( $redirect_data_slo['location'] );
		$this->assertNotFalse( $logout_url_slo );
		$this->assertEquals( 'https', $logout_url_slo['scheme'] );
		$this->assertEquals( 'test.auth0.com', $logout_url_slo['host'] );
		$this->assertEquals( '/v2/logout', $logout_url_slo['path'] );
		$this->assertContains( 'client_id=__test_client_id__', $logout_url_slo['query'] );
		$this->assertContains( 'returnTo=' . rawurlencode( home_url() ), $logout_url_slo['query'] );
	}

	/**
	 * Test that a redirect to the homepage happens if ULP is turned on.
	 */
	public function testThatRedirectHappensIfUlp() {
		$this->startRedirectHalting();
		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'singlelogout', 0 );
		self::$opts->set( 'auto_login', 1 );

		$redirect_data = [];
		try {
			wp_auth0_process_logout();
		} catch ( Exception $e ) {
			$redirect_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 302, $redirect_data['status'] );
		$this->assertEquals( home_url(), $redirect_data['location'] );
	}
}
