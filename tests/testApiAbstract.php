<?php
/**
 * Contains Class TestApiAbstract.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestApiAbstract.
 * Test the WP_Auth0_Api_Abstract class.
 */
class TestApiAbstract extends TestCase {

	use HookHelpers;

	use HttpHelpers;

	use SetUpTestDb;

	/**
	 * Test domain to use.
	 */
	const TEST_DOMAIN = 'test.domain.com';

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	protected static $options;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * Set up before test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$options   = WP_Auth0_Options::Instance();
		self::$error_log = new WP_Auth0_ErrorLog();
	}

	/**
	 * Test that the basic class setup happens properly.
	 */
	public function testSetup() {
		self::$options->set( 'domain', self::TEST_DOMAIN );

		// 1. Test that the default URL was set correctly.
		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/', $api_abstract->get_request( 'url' ) );

		// 2. Test that we have an analytics header being sent with the correct data.
		$headers = $api_abstract->get_request( 'headers' );
		$this->assertNotEmpty( $headers );
		$this->assertNotEmpty( $headers['Auth0-Client'] );

		$client_header = base64_decode( $headers['Auth0-Client'] );
		$client_header = json_decode( $client_header, true );

		$this->assertEquals( 'wp-auth0', $client_header['name'] );
		$this->assertEquals( WPA0_VERSION, $client_header['version'] );
	}

	/**
	 * Test that headers are set properly.
	 */
	public function testHeaders() {
		$mock_abstract = new ReflectionClass( Test_WP_Auth0_Api_Abstract::class );
		$method        = $mock_abstract->getMethod( 'add_header' );
		$method->setAccessible( true );

		// 1. Test a basic value.
		$class = $method->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), '__test_key_1__', '__test_val_1__' );
		$this->assertEquals( '__test_val_1__', $class->get_request( 'headers' )['__test_key_1__'] );

		// 2. Test another basic value.
		$class = $method->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), '__test_key_2__', '__test_val_2__' );
		$this->assertEquals( '__test_val_2__', $class->get_request( 'headers' )['__test_key_2__'] );

		// 3. Test that existing values are overwritten.
		$class = $method->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), '__test_key_1__', '__test_val_3__' );
		$this->assertEquals( '__test_val_3__', $class->get_request( 'headers' )['__test_key_1__'] );
	}

	/**
	 * Test that the path is set properly.
	 */
	public function testSetPath() {
		self::$options->set( 'domain', self::TEST_DOMAIN );

		// Reflect the Test_WP_Auth0_Api_Abstract class to set 2 methods as public.
		$mock_abstract = new ReflectionClass( Test_WP_Auth0_Api_Abstract::class );
		$set_path      = $mock_abstract->getMethod( 'set_path' );
		$set_path->setAccessible( true );
		$build_url = $mock_abstract->getMethod( 'build_url' );
		$build_url->setAccessible( true );

		// 1. Make sure a basic path is added successfully.
		$class = $set_path->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), 'path' );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/path', $class->get_request( 'url' ) );
		$this->assertEquals( $class->get_request( 'url' ), $build_url->invoke( $class ) );

		// 2. Make sure a leading slash is cleared before adding.
		$class = $set_path->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), '/path' );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/path', $class->get_request( 'url' ) );
		$this->assertEquals( $class->get_request( 'url' ), $build_url->invoke( $class ) );

		// 3. Make sure a trailing slash is included.
		$class = $set_path->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), 'path/' );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/path/', $class->get_request( 'url' ) );
		$this->assertEquals( $class->get_request( 'url' ), $build_url->invoke( $class ) );

		// 4. Make sure a more complex path can be added.
		$class = $set_path->invoke( new Test_WP_Auth0_Api_Abstract( self::$options ), 'multi/path' );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/multi/path', $class->get_request( 'url' ) );
		$this->assertEquals( $class->get_request( 'url' ), $build_url->invoke( $class ) );

		// 5. Make sure the path is overwritten, not appended.
		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );
		$set_path->invoke( $api_abstract, 'path1' );
		$set_path->invoke( $api_abstract, 'path2' );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/path2', $api_abstract->get_request( 'url' ) );
		$this->assertEquals( $api_abstract->get_request( 'url' ), $build_url->invoke( $api_abstract ) );
	}

	/**
	 * Test that the body is modified properly.
	 */
	public function testSendBodyMethods() {
		$this->startHttpHalting();

		self::$options->set( 'domain', self::TEST_DOMAIN );
		self::$options->set( 'client_id', '__test_client_id__' );
		self::$options->set( 'client_secret', '__test_client_secret__' );

		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );

		// Reflect the class to set 1 method as public.
		$mock_abstract = new ReflectionClass( Test_WP_Auth0_Api_Abstract::class );
		$send_audience = $mock_abstract->getMethod( 'send_audience' );
		$send_audience->setAccessible( true );

		// 1. Test that the audience is set correctly when using a path.
		$api_abstract = $send_audience->invoke( $api_abstract );
		$this->assertEquals(
			'https://' . self::TEST_DOMAIN . '/api/v2/',
			$api_abstract->get_request( 'body' )['audience']
		);

		// 2. Test that the client_id is set.
		$send_client_id = $mock_abstract->getMethod( 'send_client_id' );
		$send_client_id->setAccessible( true );
		$api_abstract = $send_client_id->invoke( $api_abstract );
		$this->assertEquals( '__test_client_id__', $api_abstract->get_request( 'body' )['client_id'] );

		// 3. Test that the client_secret is set.
		$send_client_secret = $mock_abstract->getMethod( 'send_client_secret' );
		$send_client_secret->setAccessible( true );
		$api_abstract = $send_client_secret->invoke( $api_abstract );
		$this->assertEquals( '__test_client_secret__', $api_abstract->get_request( 'body' )['client_secret'] );

		// 4. Test an arbitrary body value.
		$add_body = $mock_abstract->getMethod( 'add_body' );
		$add_body->setAccessible( true );
		$api_abstract = $add_body->invoke( $api_abstract, '__test_key__', '__test_val__' );
		$this->assertEquals( '__test_val__', $api_abstract->get_request( 'body' )['__test_key__'] );

		// 5. Make sure all keys set previously are sent with the request.
		$decoded_res = [];
		try {
			$api_abstract->set_http_method( 'get' )->call();
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/api/v2/', $decoded_res['body']['audience'] );
		$this->assertEquals( '__test_client_id__', $decoded_res['body']['client_id'] );
		$this->assertEquals( '__test_client_secret__', $decoded_res['body']['client_secret'] );
		$this->assertEquals( '__test_val__', $decoded_res['body']['__test_key__'] );
	}

	/**
	 * Test basic HTTP request methods.
	 *
	 * @throws Exception - If the method passed to set_http_method does not exist.
	 */
	public function testHttpRequests() {
		$this->startHttpHalting();
		self::$options->set( 'domain', self::TEST_DOMAIN );

		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );

		// 1. Test a basic GET request.
		$decoded_res = [];
		try {
			$api_abstract->set_http_method( 'get' )->call();
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/', $decoded_res['url'] );
		$this->assertEquals( 'GET', $decoded_res['method'] );
		$this->assertNotEmpty( $decoded_res['headers']['Auth0-Client'] );

		// 2. Test a basic DELETE request.
		$decoded_res = [];
		try {
			$api_abstract->set_http_method( 'delete' )->call();
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/', $decoded_res['url'] );
		$this->assertEquals( 'DELETE', $decoded_res['method'] );
		$this->assertNotEmpty( $decoded_res['headers']['Auth0-Client'] );

		// 4. Test a basic POST request.
		$decoded_res = [];
		try {
			$api_abstract->set_http_method( 'post' )->call();
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/', $decoded_res['url'] );
		$this->assertEquals( 'POST', $decoded_res['method'] );
		$this->assertEquals( 'application/json', $decoded_res['headers']['Content-Type'] );
		$this->assertNotEmpty( $decoded_res['headers']['Auth0-Client'] );

		// 5. Test a basic PATCH request.
		$decoded_res = [];
		try {
			$api_abstract->set_http_method( 'patch' )->call();
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/', $decoded_res['url'] );
		$this->assertEquals( 'PATCH', $decoded_res['method'] );
		$this->assertEquals( 'application/json', $decoded_res['headers']['Content-Type'] );
		$this->assertNotEmpty( $decoded_res['headers']['Auth0-Client'] );
	}

	/**
	 * Test that a WP_Error as a response is handled properly.
	 *
	 * @throws Exception - If the set_http_method is not called with a valid HTTP method.
	 */
	public function testHandleWpError() {
		$this->startHttpMocking();

		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );

		$this->http_request_type = 'wp_error';
		$this->assertEquals( 'caught_wp_error', $api_abstract->set_http_method( 'get' )->call() );
		$this->assertCount( 1, self::$error_log->get() );
	}

	/**
	 * Test that an Auth0 server error as a response is handled properly.
	 *
	 * @throws Exception - If the set_http_method is not called with a valid HTTP method.
	 */
	public function testHandleAuth0FailedResponse() {
		$this->startHttpMocking();

		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );

		// 1. Test that a successful call does not log an error.
		$this->http_request_type = 'success_empty_body';
		$this->assertEquals( 'completed_successfully', $api_abstract->call() );
		$this->assertEmpty( self::$error_log->get() );

		// 2. Test that a typical Auth0 API error is logged properly.
		$this->http_request_type = 'auth0_api_error';
		$this->assertEquals( 'caught_failed_response', $api_abstract->call() );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );
		$this->assertEquals( 'Error returned - Error [error_code]', $log[0]['message'] );

		// 3. Test that a typical Auth0 callback error is logged properly.
		$this->http_request_type = 'auth0_callback_error';
		$this->assertEquals( 'caught_failed_response', $api_abstract->call() );
		$log = self::$error_log->get();
		$this->assertCount( 2, $log );
		$this->assertEquals( 'caught_callback_error', $log[0]['code'] );
		$this->assertEquals( 'Error returned - Error', $log[0]['message'] );
	}

	/**
	 * Test that an unspecified server error response is logged properly.
	 */
	public function testHandleOtherFailedResponse() {
		$this->startHttpMocking();

		$api_abstract = new Test_WP_Auth0_Api_Abstract( self::$options );

		$this->http_request_type = 'other_error';
		$this->assertEquals( 'caught_failed_response', $api_abstract->call() );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( '{"other_error":"Other error"}', $log[0]['message'] );
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();
		$this->stopHttpHalting();
		$this->stopHttpMocking();
		self::$error_log->clear();
	}
}
