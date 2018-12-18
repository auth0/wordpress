<?php
/**
 * Contains Class TestRoutesLogin.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestRoutesLogin.
 *
 * @group routes
 */
class TestRoutesLogin extends TestCase {

	use HookHelpers;

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
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		$_POST    = [];
		$_GET     = [];
		$_REQUEST = [];

		parent::setUp();
		$this->setUpDb();
		self::$opts->reset();
		self::$wp = new WP_Query();
		self::$wp->set( 'custom_requests_return', true );
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$error_log->clear();
	}

	/**
	 * If migration services are off, the route should fail with an error.
	 */
	public function testThatLoginRouteIsForbiddenByDefault() {
		self::$wp->query_vars['a0_action'] = 'migration-ws-login';

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 403, $output->status );
		$this->assertEquals( 'Forbidden', $output->error );

		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * If the incoming IP address is invalid, the route should fail with an error.
	 */
	public function testThatLoginRouteIsUnauthorizedIfWrongIp() {
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'migration_ips_filter', 1 );
		self::$wp->query_vars['a0_action'] = 'migration-ws-login';

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Unauthorized', $output->error );

		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * If there is no token, the route should fail with an error.
	 */
	public function testThatLoginRouteIsUnauthorizedIfNoToken() {
		self::$opts->set( 'migration_ws', 1 );
		self::$wp->query_vars['a0_action'] = 'migration-ws-login';

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Unauthorized: missing authorization header', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If the token has the wrong JTI, the route should fail with an error.
	 */
	public function testThatLoginRouteIsUnauthorizedIfWrongJti() {
		$client_secret = '__test_client_secret__';
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token_id', '__test_token_id__' );

		self::$wp->query_vars['a0_action'] = 'migration-ws-login';
		$_POST['access_token']             = JWT::encode( [ 'jti' => uniqid() ], $client_secret );

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Invalid token', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If the token has the wrong JTI, the route should fail with an error.
	 */
	public function testThatLoginRouteIsUnauthorizedIfMissingJti() {
		$client_secret = '__test_client_secret__';
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token_id', '__test_token_id__' );

		self::$wp->query_vars['a0_action'] = 'migration-ws-login';
		$_POST['access_token']             = JWT::encode( [ 'iss' => uniqid() ], $client_secret );

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Invalid token', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If there is no username POSTed, the route should fail with an error.
	 */
	public function testThatLoginRouteIsBadRequestIfNoUsername() {
		$client_secret   = '__test_client_secret__';
		$token_id        = '__test_token_id__';
		$migration_token = JWT::encode( [ 'jti' => $token_id ], $client_secret );
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token', $migration_token );

		self::$wp->query_vars['a0_action'] = 'migration-ws-login';
		$_POST['access_token']             = $migration_token;

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 400, $output->status );
		$this->assertEquals( 'Username is required', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If there is no password POSTed, the route should fail with an error.
	 */
	public function testThatLoginRouteIsBadRequestIfNoPassword() {
		$client_secret   = '__test_client_secret__';
		$token_id        = '__test_token_id__';
		$migration_token = JWT::encode( [ 'jti' => $token_id ], $client_secret );
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token', $migration_token );

		self::$wp->query_vars['a0_action'] = 'migration-ws-login';
		$_POST['access_token']             = $migration_token;
		$_POST['username']                 = uniqid();

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 400, $output->status );
		$this->assertEquals( 'Password is required', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If there the username or password are incorrect, the route should fail with an error.
	 */
	public function testThatLoginRouteIsUnauthorizedIfNotAuthenticated() {
		$client_secret   = '__test_client_secret__';
		$token_id        = '__test_token_id__';
		$migration_token = JWT::encode( [ 'jti' => $token_id ], $client_secret );
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token', $migration_token );

		self::$wp->query_vars['a0_action'] = 'migration-ws-login';

		$_POST['access_token'] = $migration_token;
		$_POST['username']     = uniqid();
		$_POST['password']     = uniqid();

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Invalid Credentials', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * Route should return a user with no password set if provided a valid username and password.
	 */
	public function testThatLoginRouteReturnsUserIfSuccessful() {
		$client_secret     = '__test_client_secret__';
		$token_id          = '__test_token_id__';
		$_POST['username'] = uniqid() . '@' . uniqid() . '.com';
		$_POST['password'] = uniqid();
		$user              = $this->createUser(
			[
				'user_email' => $_POST['username'],
				'user_pass'  => $_POST['password'],
			]
		);
		$migration_token   = JWT::encode( [ 'jti' => $token_id ], $client_secret );
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token', $migration_token );

		self::$wp->query_vars['a0_action'] = 'migration-ws-login';
		$_POST['access_token']             = $migration_token;

		$output = json_decode( self::$routes->custom_requests( self::$wp ) );

		$this->assertEquals( $user->ID, $output->data->ID );
		$this->assertEquals( $user->user_login, $output->data->user_login );
		$this->assertEquals( $user->user_email, $output->data->user_email );
		$this->assertEquals( $user->display_name, $output->data->display_name );
		$this->assertObjectNotHasAttribute( 'user_pass', $output->data );

		$this->assertEmpty( self::$error_log->get() );
	}
}
