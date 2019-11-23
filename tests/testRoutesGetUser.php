<?php
/**
 * Contains Class TestRoutesGetUser.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

/**
 * Class TestRoutesGetUser.
 *
 * @group routes
 */
class TestRoutesGetUser extends WP_Auth0_Test_Case {

	use HookHelpers;

	use TokenHelper;
	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Routes.
	 *
	 * @var WP_Auth0_Routes
	 */
	public static $routes;

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
		self::$routes = new WP_Auth0_Routes( self::$opts );
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		$_POST    = [];
		$_GET     = [];
		$_REQUEST = [];

		parent::setUp();
		self::$wp                          = new WP();
		self::$wp->query_vars['a0_action'] = 'migration-ws-get-user';
	}

	/**
	 * If migration services are off, the route should fail with an error.
	 */
	public function testThatGetUserRouteIsForbiddenByDefault() {
		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( 403, $output->status );
		$this->assertEquals( 'Forbidden', $output->error );

		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * If the incoming IP address is invalid, the route should fail with an error.
	 */
	public function testThatGetUserRouteIsUnauthorizedIfWrongIp() {
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'migration_ips_filter', 1 );

		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Unauthorized', $output->error );
		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * If there is no token, the route should fail with an error.
	 */
	public function testThatGetUserRouteIsUnauthorizedIfNoToken() {
		self::$opts->set( 'migration_ws', 1 );

		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Unauthorized: missing authorization header', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If the token has the wrong JTI, the route should fail with an error.
	 */
	public function testThatGetUserRouteIsUnauthorizedIfWrongJti() {
		$client_secret = '__test_client_secret__';
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'client_secret', $client_secret );
		self::$opts->set( 'migration_token_id', '__test_token_id__' );

		$_POST['access_token'] = self::makeToken( [ 'jti' => uniqid() ], $client_secret );

		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'Invalid token', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If there is no username POSTed, the route should fail with an error.
	 */
	public function testThatGetUserRouteIsBadRequestIfNoUsername() {
		$migration_token = uniqid();
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'migration_token', $migration_token );

		$_POST['access_token'] = $migration_token;

		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( 400, $output->status );
		$this->assertEquals( 'Username is required', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * If there the username cannot be found, the route should fail with an error.
	 */
	public function testThatGetUserRouteIsUnauthorizedIfUserNotFound() {
		$migration_token = uniqid();
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'migration_token', $migration_token );

		$_POST['access_token'] = $migration_token;
		$_POST['username']     = uniqid();

		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( 401, $output->status );
		$this->assertEquals( 'User not found', $output->error );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( $output->error, $log[0]['message'] );
	}

	/**
	 * Route should return a blank user profile when email is being updated.
	 */
	public function testThatGetUserRouteReturnsEmptyIfEmailUpdate() {
		$migration_token = uniqid();
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'migration_token', $migration_token );

		$_POST['access_token'] = $migration_token;
		$_POST['username']     = uniqid() . '@' . uniqid() . '.com';

		$user = $this->createUser( [ 'user_email' => $_POST['username'] ] );
		WP_Auth0_UsersRepo::update_meta( $user->ID, 'auth0_transient_email_update', $_POST['username'] );

		$output = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertFalse( isset( $output->ID ) );
		$this->assertEquals( 200, $output->status );
		$this->assertEquals( 'Email update in process', $output->error );
		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * Route should return a user with no password set if provided a valid username or email.
	 */
	public function testThatGetUserRouteReturnsUserIfSuccessful() {
		$_POST['username'] = uniqid() . '@' . uniqid() . '.com';
		$user              = $this->createUser( [ 'user_email' => $_POST['username'] ] );

		$migration_token = uniqid();
		self::$opts->set( 'migration_ws', 1 );
		self::$opts->set( 'migration_token', $migration_token );

		$_POST['access_token'] = $migration_token;

		$output_em = json_decode( self::$routes->custom_requests( self::$wp, true ) );

		$this->assertEquals( $user->ID, $output_em->data->ID );
		$this->assertEquals( $user->user_login, $output_em->data->user_login );
		$this->assertEquals( $user->user_email, $output_em->data->user_email );
		$this->assertEquals( $user->display_name, $output_em->data->display_name );
		$this->assertObjectNotHasAttribute( 'user_pass', $output_em->data );
		$this->assertEmpty( self::$error_log->get() );
	}
}
