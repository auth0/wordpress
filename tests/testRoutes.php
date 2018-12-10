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
 */
class TestRoutes extends TestCase {

	use HookHelpers;

	use SetUpTestDb {
		setUp as setUpDb;
	}

	use UsersHelper;

	/**
	 * Default query_vars state.
	 */
	const WP_OBJECT_DEFAULT = [ 'query_vars' => [ 'custom_requests_return' => true ] ];

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
	 * @var stdClass|WP_Query
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
		self::$wp        = (object) self::WP_OBJECT_DEFAULT;
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();
		$this->setUpDb();
		self::$wp = (object) self::WP_OBJECT_DEFAULT;
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
}
