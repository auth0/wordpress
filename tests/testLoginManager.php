<?php
/**
 * Contains Class TestLoginManager.
 *
 * @package WP-Auth0
 * @since 3.7.1
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestLoginManager.
 * Tests that WP_Auth0_LoginManager methods function as expected.
 */
class TestLoginManager extends TestCase {

	use OptionsHelpers;

	use RedirectHelpers;

	use SetUpTestDb;

	use UsersHelper;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * WP_Auth0_UsersRepo instance.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	protected static $users_repo;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts       = WP_Auth0_Options::Instance();
		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		self::$error_log  = new WP_Auth0_ErrorLog();
	}

	/**
	 * Run after each test.
	 */
	public function tearDown() {
		parent::tearDown();
		self::auth0Ready( false );
		$this->stopRedirectHalting();
		self::$error_log->clear();
		self::$opts->set( 'custom_domain', '' );
		self::$opts->set( 'auth0_implicit_workflow', false );
		self::$opts->set( 'auto_login', 0 );
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
	 * Test that authorize URL params are built and filtered properly.
	 */
	public function testAuthorizeParams() {
		$test_client_id  = uniqid();
		$test_connection = uniqid();
		$auth_params     = WP_Auth0_LoginManager::get_authorize_params();

		$this->assertEquals( 'openid email profile', $auth_params['scope'] );
		$this->assertEquals( 'code', $auth_params['response_type'] );
		$this->assertEquals( site_url( 'index.php?auth0=1' ), $auth_params['redirect_uri'] );
		$this->assertNotEmpty( $auth_params['auth0Client'] );
		$this->assertNotEmpty( $auth_params['state'] );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( $test_connection );
		$this->assertEquals( $test_connection, $auth_params['connection'] );

		self::$opts->set( 'client_id', $test_client_id );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params();
		$this->assertEquals( $test_client_id, $auth_params['client_id'] );

		self::$opts->set( 'auth0_implicit_workflow', 1 );
		$auth_params = WP_Auth0_LoginManager::get_authorize_params();
		$this->assertEquals( add_query_arg( 'auth0', 'implicit', site_url( 'index.php' ) ), $auth_params['redirect_uri'] );
		$this->assertEquals( 'id_token', $auth_params['response_type'] );
		$this->assertNotEmpty( $auth_params['nonce'] );
		$this->assertEquals( 'form_post', $auth_params['response_mode'] );

		add_filter(
			'auth0_authorize_url_params',
			function( $params, $connection, $redirect_to ) {
				$params[ $connection ] = $redirect_to;
				return $params;
			},
			10,
			3
		);

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( 'auth0', 'https://auth0.com' );
		$this->assertEquals( 'https://auth0.com', $auth_params['auth0'] );
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
		$caught_redirect = [];
		try {
			// Need to hide error messages here because a cookie is set.
			// phpcs:ignore
			@$login_manager->login_auto();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 302, $caught_redirect['status'] );

		// Test that request method will stop redirect.
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertFalse( $login_manager->login_auto() );

		$_SERVER['REQUEST_METHOD'] = 'PATCH';
		$this->assertFalse( $login_manager->login_auto() );
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
		$_GET['wle'] = 1;
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
		$_GET['action'] = 'logout';
		$this->assertFalse( $login_manager->login_auto() );
	}

	/**
	 * Test that the ULP redirect does not happen if wp-login.php is used as a callback.
	 */
	public function testThatUlpRedirectIsSkippedForCallback() {
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

		// Test that the auth0 URL param will skip the redirect.
		$_REQUEST['auth0'] = 1;
		$this->assertFalse( $login_manager->login_auto() );
	}
}
