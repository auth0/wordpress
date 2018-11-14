<?php
/**
 * Contains Class TestApiOperations.
 *
 * @package WP-Auth0
 *
 * @since 3.8.1
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestApiOperations.
 */
class TestApiOperations extends TestCase {

	use HttpHelpers;

	use RedirectHelpers;

	use SetUpTestDb;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts      = WP_Auth0_Options::Instance();
		self::$error_log = new WP_Auth0_ErrorLog();
	}

	/**
	 * Test that a basic create connection command requests properly.
	 */
	public function testThatCreateConnectionRequestsCorrectly() {
		$this->startHttpHalting();

		$api_ops    = new WP_Auth0_Api_Operations( self::$opts );
		$test_token = implode( '.', [ uniqid(), uniqid(), uniqid() ] );

		self::$opts->set( 'domain', 'test-wp.auth0.com' );
		self::$opts->set( 'client_id', 'TEST_CLIENT_ID' );

		$caught_http = [];
		try {
			$api_ops->create_wordpress_connection( $test_token, false, 'good' );
		} catch ( Exception $e ) {
			$caught_http = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test-wp.auth0.com/api/v2/connections', $caught_http['url'] );
		$this->assertEquals( 'Bearer ' . $test_token, $caught_http['headers']['Authorization'] );
		$this->assertEquals( 'DB-Test-Blog', $caught_http['body']['name'] );
		$this->assertEquals( 'auth0', $caught_http['body']['strategy'] );
		$this->assertEquals( 'good', $caught_http['body']['options']['passwordPolicy'] );
		$this->assertContains( 'TEST_CLIENT_ID', $caught_http['body']['enabled_clients'] );
	}

	/**
	 * Test that a migration create connection command requests properly.
	 */
	public function testThatCreateConnectionWithMigrationRequestsCorrectly() {
		$this->startHttpHalting();

		$api_ops    = new WP_Auth0_Api_Operations( self::$opts );
		$test_token = implode( '.', [ uniqid(), uniqid(), uniqid() ] );

		self::$opts->set( 'domain', 'test-wp2.auth0.com' );
		self::$opts->set( 'client_id', 'TEST_CLIENT_ID_2' );

		$caught_http = [];
		try {
			$api_ops->create_wordpress_connection( $test_token, true, 'fair', 'TEST_MIGRATION_TOKEN' );
		} catch ( Exception $e ) {
			$caught_http = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test-wp2.auth0.com/api/v2/connections', $caught_http['url'] );
		$this->assertEquals( 'Bearer ' . $test_token, $caught_http['headers']['Authorization'] );
		$this->assertEquals( 'DB-Test-Blog', $caught_http['body']['name'] );
		$this->assertEquals( 'auth0', $caught_http['body']['strategy'] );
		$this->assertContains( 'TEST_CLIENT_ID_2', $caught_http['body']['enabled_clients'] );

		$this->assertEquals( true, $caught_http['body']['options']['requires_username'] );
		$this->assertEquals( true, $caught_http['body']['options']['import_mode'] );
		$this->assertEquals( true, $caught_http['body']['options']['enabledDatabaseCustomization'] );
		$this->assertEquals(
			[
				'min' => 1,
				'max' => 100,
			],
			$caught_http['body']['options']['validation']['username']
		);

		$this->assertContains(
			'request.post("http://example.org/index.php?a0_action=migration-ws-login"',
			$caught_http['body']['options']['customScripts']['login']
		);

		$this->assertContains(
			'form:{username:email, password:password, access_token:"TEST_MIGRATION_TOKEN"}',
			$caught_http['body']['options']['customScripts']['login']
		);

		$this->assertContains(
			'request.post("http://example.org/index.php?a0_action=migration-ws-get-user"',
			$caught_http['body']['options']['customScripts']['get_user']
		);

		$this->assertContains(
			'form:{username:email, access_token:"TEST_MIGRATION_TOKEN',
			$caught_http['body']['options']['customScripts']['get_user']
		);
	}

	/**
	 * Test that successful and unsuccessful requests return properly.
	 */
	public function testThatCreateConnectionReturnsCorrectly() {
		$this->startHttpMocking();

		$api_ops    = new WP_Auth0_Api_Operations( self::$opts );
		$test_token = implode( '.', [ uniqid(), uniqid(), uniqid() ] );

		self::$opts->set( 'domain', 'test-wp.auth0.com' );
		self::$opts->set( 'client_id', 'TEST_CLIENT_ID' );

		$this->http_request_type = 'success_create_connection';

		$result = $api_ops->create_wordpress_connection( $test_token, false );

		$this->assertEquals( 'TEST_CREATED_CONN_ID', $result );
		$this->assertEquals( 'DB-Test-Blog', self::$opts->get( 'db_connection_name' ) );

		$this->http_request_type = 'wp_error';

		$result = $api_ops->create_wordpress_connection( $test_token, false );

		$this->assertFalse( $result );
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();

		self::$opts->set( 'domain', self::$opts->get_default( 'domain' ) );
		self::$opts->set( 'client_id', self::$opts->get_default( 'client_id' ) );
		self::$opts->set( 'migration_ips', self::$opts->get_default( 'migration_ips' ) );
		self::$opts->set( 'migration_ips_filter', self::$opts->get_default( 'migration_ips_filter' ) );
		self::$opts->set( 'db_connection_name', null );

		$this->stopHttpHalting();
		$this->stopHttpMocking();

		self::$error_log->clear();
	}
}
