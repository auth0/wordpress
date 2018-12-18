<?php
/**
 * Contains Class TestRoutes.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestRoutes.
 *
 * @group routes
 */
class TestRoutes extends TestCase {

	use HookHelpers;

	use OptionsHelpers;

	use SetUpTestDb {
		setUp as setUpDb;
	}

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Instance of WP_Auth0_Routes.
	 *
	 * @var WP_Auth0_Routes
	 */
	public static $routes;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * Mock WP instance.
	 *
	 * @var WP
	 */
	protected static $wp;

	/**
	 * Run before test suite.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts   = WP_Auth0_Options::Instance();
		self::$routes = new WP_Auth0_Routes( self::$opts );

		self::$error_log = new WP_Auth0_ErrorLog();
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();
		$this->setUpDb();
		self::$wp = new WP();
		self::$wp->set_query_var( 'custom_requests_return', true );
		self::$opts->reset();
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$error_log->clear();
	}

	/**
	 * If we have no query vars, the route should do nothing.
	 */
	public function testThatEmptyQueryVarsDoesNothing() {
		$this->assertNull( self::$routes->custom_requests( self::$wp ) );
	}

	/**
	 * If we have no valid query vars, the route should do nothing.
	 */
	public function testThatUnknownRouteDoesNothing() {
		self::$wp->query_vars['a0_action'] = uniqid();
		$this->assertFalse( self::$routes->custom_requests( self::$wp ) );

		unset( self::$wp->query_vars['a0_action'] );
		self::$wp->query_vars['pagename'] = uniqid();
		$this->assertFalse( self::$routes->custom_requests( self::$wp ) );

		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * Test that the OAuth configuration returns the correct values.
	 */
	public function testThatOauthConfigIsCorrect() {
		self::$wp->set_query_var( 'a0_action', 'oauth2-config' );

		$output = json_decode( self::$routes->custom_requests( self::$wp ), true );

		$this->assertEquals( 'Test Blog', $output['client_name'] );
		$this->assertCount( 1, $output['redirect_uris'] );
		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-setup&callback=1',
			$output['redirect_uris'][0]
		);

		self::$wp->set_query_var( 'a0_action', null );
		self::$wp->set_query_var( 'pagename', 'oauth2-config' );
		$output_2 = json_decode( self::$routes->custom_requests( self::$wp ), true );
		$this->assertEquals( $output, $output_2 );
	}

	/**
	 * Test that the COO fallback outputs the correct values.
	 */
	public function testThatCooFallbackIsCorrect() {
		self::auth0Ready( true );
		self::$wp->set_query_var( 'a0_action', 'coo-fallback' );

		$output = self::$routes->custom_requests( self::$wp );

		$this->assertContains( '<script src="' . WPA0_AUTH0_JS_CDN_URL . '"></script>', $output );
		$this->assertContains( 'var auth0 = new auth0.WebAuth({', $output );
		$this->assertContains( 'clientID:"' . self::$opts->get( 'client_id' ) . '"', $output );
		$this->assertContains( 'domain:"' . self::$opts->get( 'domain' ) . '"', $output );
		$this->assertContains( 'redirectUri:"http://example.org/index.php?auth0=1"', $output );
		$this->assertContains( 'auth0.crossOriginAuthenticationCallback()', $output );

		self::$wp->set_query_var( 'a0_action', null );
		self::$wp->set_query_var( 'auth0fallback', 1 );
		$output_2 = self::$routes->custom_requests( self::$wp );
		$this->assertEquals( $output, $output_2 );
	}
}
