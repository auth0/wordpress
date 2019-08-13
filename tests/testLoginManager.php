<?php
/**
 * Contains Class TestLoginManager.
 *
 * @package WP-Auth0
 *
 * @since 3.7.1
 */

/**
 * Class TestLoginManager.
 * Tests that WP_Auth0_LoginManager methods function as expected.
 */
class TestLoginManager extends WP_Auth0_Test_Case {

	use RedirectHelpers;

	use UsersHelper;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
	}

	/**
	 * Test that the default auth scopes are returned and filtered properly.
	 */
	public function testUserinfoScope() {
		$scope = WP_Auth0_LoginManager::get_userinfo_scope();
		$this->assertEquals( 'openid email profile', $scope );

		add_filter(
			'auth0_auth_scope',
			function( $default_scope, $context ) {
				$default_scope[] = $context;
				return $default_scope;
			},
			10,
			2
		);

		$scope = WP_Auth0_LoginManager::get_userinfo_scope( 'auth0' );
		$this->assertEquals( 'openid email profile auth0', $scope );
	}

	/**
	 * Test that the authorize URL is built properly.
	 */
	public function testBuildAuthorizeUrl() {

		// Basic authorize URL.
		self::$opts->set( 'domain', 'test.auth0.com' );
		$auth_url = WP_Auth0_LoginManager::build_authorize_url();

		$this->assertEquals( 'https://test.auth0.com/authorize', $auth_url );

		// Custom domain authorize URL.
		self::$opts->set( 'custom_domain', 'test-custom.auth0.com' );
		$auth_url = WP_Auth0_LoginManager::build_authorize_url();

		$this->assertEquals( 'https://test-custom.auth0.com/authorize', $auth_url );

		// Authorize URL with parameters.
		$auth_url = WP_Auth0_LoginManager::build_authorize_url(
			[
				'connection' => 'auth0',
				'prompt'     => 'none',
			]
		);

		$this->assertEquals( 'https://test-custom.auth0.com/authorize?connection=auth0&prompt=none', $auth_url );

		// Authorize URL with parameters that are URL encoded.
		$auth_url = WP_Auth0_LoginManager::build_authorize_url( [ 'connection' => 'this/that' ] );

		$this->assertEquals( 'https://test-custom.auth0.com/authorize?connection=this%2Fthat', $auth_url );

		// Authorize URL filter.
		add_filter(
			'auth0_authorize_url',
			function ( $auth_url, $params ) {
				return explode( '?', $auth_url )[0] . '?test=' . $params['test'];
			},
			10,
			2
		);

		$auth_url = WP_Auth0_LoginManager::build_authorize_url(
			[
				'test'       => 'this',
				'connection' => 'auth0',
			]
		);
		$this->assertEquals( 'https://test-custom.auth0.com/authorize?test=this', $auth_url );
	}

	/**
	 * Test that logged-in users are redirected from the wp-login.php page.
	 */
	public function testThatLoggedInUserIsRedirectedFromWpLogin() {
		$this->startRedirectHalting();

		$login_manager = new WP_Auth0_LoginManager( self::$users_repo, self::$opts );

		// Configure Auth0.
		self::auth0Ready();
		$this->assertTrue( WP_Auth0::ready() );

		// Set the current user to admin.
		$this->setGlobalUser();

		// Use the default login redirection.
		$caught_redirect = [
			'location' => null,
			'status'   => null,
		];
		try {
			$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}

		// Redirect will have a dynamic cache breaker so cannot assertEquals here.
		$this->assertNotFalse( strpos( $caught_redirect['location'], 'http://example.org?' ) );
		$this->assertEquals( 302, $caught_redirect['status'] );

		// Set a one-time login redirect URL.
		$_REQUEST['redirect_to'] = 'http://example.org/custom';

		// Use the default login redirection.
		$caught_redirect = [
			'location' => null,
			'status'   => null,
		];
		try {
			$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}

		// Redirect will have a dynamic cache breaker so cannot assertEquals here.
		$this->assertNotFalse( strpos( $caught_redirect['location'], 'http://example.org/custom?' ) );
		$this->assertEquals( 302, $caught_redirect['status'] );
	}

	/**
	 * Test that the ULP redirect happens.
	 */
	public function testUlpRedirect() {
		$this->startRedirectHalting();

		$login_manager = new WP_Auth0_LoginManager( self::$users_repo, self::$opts );
		$this->assertFalse( $login_manager->login_auto() );

		// Activate settings that result in a ULP redirect.
		self::$opts->set( 'auto_login', 1 );
		self::auth0Ready( true );
		self::$opts->set( 'domain', 'test-wp.auth0.com' );
		$this->assertTrue( WP_Auth0::ready() );

		$caught_redirect = [
			'location' => null,
			'status'   => null,
		];
		try {
			// Need to hide error messages here because a cookie is set.
			// phpcs:ignore
			@$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}

		$this->assertNotFalse( strpos( $caught_redirect['location'], 'https://test-wp.auth0.com/authorize?' ) );
		$this->assertEquals( 302, $caught_redirect['status'] );
	}

	/**
	 * Test that the ULP redirect does not happen for non-GET methods.
	 */
	public function testThatUlpRedirectIsSkippedForNonGetMethod() {
		$this->startRedirectHalting();

		$login_manager = new WP_Auth0_LoginManager( self::$users_repo, self::$opts );

		// First, check that a redirect is happening.
		self::$opts->set( 'auto_login', 1 );
		self::auth0Ready( true );
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$caught_redirect           = [];
		try {
			// Need to hide error messages here because a cookie is set.
			// phpcs:ignore
			@$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 302, $caught_redirect['status'] );
	}

	/**
	 * Test that the ULP redirect does not happen if we're loading the WP login form.
	 */
	public function testThatUlpRedirectIsSkippedForWleOverride() {
		$this->startRedirectHalting();

		$login_manager = new WP_Auth0_LoginManager( self::$users_repo, self::$opts );
		$this->assertFalse( $login_manager->login_auto() );

		// First, check that a redirect is happening.
		self::$opts->set( 'auto_login', 1 );
		self::auth0Ready( true );
		$caught_redirect = [];
		try {
			// Need to hide error messages here because a cookie is set.
			// phpcs:ignore
			@$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 302, $caught_redirect['status'] );

		// Test that WP login override will skip the redirect.
		$_REQUEST['wle'] = 1;
		$this->assertFalse( $login_manager->login_auto() );
	}

	/**
	 * Test that the ULP redirect does not happen is this is a logout action.
	 */
	public function testThatUlpRedirectIsSkippedForLogout() {
		$this->startRedirectHalting();

		$login_manager = new WP_Auth0_LoginManager( self::$users_repo, self::$opts );
		$this->assertFalse( $login_manager->login_auto() );

		// First, check that a redirect is happening.
		self::$opts->set( 'auto_login', 1 );
		self::auth0Ready( true );
		$caught_redirect = [];
		try {
			// Need to hide error messages here because a cookie is set.
			// phpcs:ignore
			@$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 302, $caught_redirect['status'] );

		// Test that logout will skip the redirect.
		$GLOBALS['pagenow'] = 'wp-login.php';
		$_REQUEST['action'] = 'logout';
		$this->assertFalse( $login_manager->login_auto() );
	}
}
