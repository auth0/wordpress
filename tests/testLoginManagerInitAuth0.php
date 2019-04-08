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
		add_filter( 'wp_die_handler', [ 'TestLoginManagerInitAuth0', 'wp_die_handler' ] );
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();
		remove_filter( 'wp_die_handler', [ 'TestLoginManagerInitAuth0', 'wp_die_handler' ] );
	}

	/**
	 * Provide the function to handle wp_die.
	 *
	 * @return array
	 */
	public static function wp_die_handler() {
		return [ 'TestLoginManagerInitAuth0', 'wp_die_die' ];
	}

	/**
	 * Handle wp_die.
	 *
	 * @param string $html - Passed-in HTML to display.
	 *
	 * @throws \Exception - Always.
	 */
	public static function wp_die_die( $html ) {
		throw new Exception( $html );
	}

	/**
	 * Test that Auth0 is not initialized if the plugin is not ready or if the callback URL is not correct.
	 */
	public function testThatNothingHappensIfNotReady() {
		$this->assertFalse( $this->login->init_auth0() );
		$_REQUEST['auth0'] = 1;
		$this->assertFalse( $this->login->init_auth0() );
		self::auth0Ready( true );
		unset( $_REQUEST['auth0'] );
		$this->assertFalse( $this->login->init_auth0() );

		$output = '';
		try {
			$_REQUEST['auth0'] = 1;
			$this->login->init_auth0();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertNotEmpty( $output );
	}

	/**
	 * Test that an error in the URL parameter stops the callback with an error.
	 */
	public function testThatErrorInUrlStopsCallback() {
		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', uniqid() );
		$_REQUEST['auth0']             = 1;
		$_REQUEST['error']             = '__test_error_code__';
		$_REQUEST['error_description'] = '__test_error_description__';
		$this->setGlobalUser();

		$output = '';
		try {
			$this->login->init_auth0();
		} catch ( Exception $e ) {
			$output = $e->getMessage();
		}

		$this->assertContains( 'There was a problem with your log in', $output );
		$this->assertContains( '__test_error_description__', $output );
		$this->assertContains( 'error code', $output );
		$this->assertContains( '__test_error_code__', $output );
		$this->assertContains( '<a href="https://test.auth0.com/v2/logout?client_id=__test_client_id__', $output );
		$this->assertFalse( is_user_logged_in() );
	}

	/**
	 * Test that a logged-in user is redirected from the callback without any processing.
	 */
	public function testThatLoggedInUserIsRedirected() {
		$this->startRedirectHalting();
		$_REQUEST['auth0'] = 1;
		self::auth0Ready();
		$this->setGlobalUser();

		$caught_redirect = [];
		try {
			$this->login->init_auth0();
		} catch ( Exception $e ) {
			$caught_redirect = unserialize( $e->getMessage() );
		}

		$this->assertEquals( self::$opts->get( 'default_login_redirection' ), $caught_redirect['location'] );
	}

	/**
	 * Test that invalid state stops the callback with an error.
	 */
	public function testThatInvalidStateStopsCallback() {
		$_REQUEST['auth0'] = 1;
		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', uniqid() );

		$output = '';
		try {
			$this->login->init_auth0();
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
