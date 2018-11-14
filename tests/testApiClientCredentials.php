<?php
/**
 * Contains Class TestApiClientCredentials.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestApiClientCredentials.
 * Test the WP_Auth0_Api_Client_Credentials class.
 */
class TestApiClientCredentials extends TestCase {

	use httpHelpers {
		httpMock as protected httpMockDefault;
	}

	use SetUpTestDb;

	/**
	 * Test API domain to use.
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
	 * Test the request sent by the Client Credentials call.
	 */
	public function testRequest() {
		$this->startHttpHalting();

		$client_id     = uniqid();
		$client_secret = uniqid();

		self::$options->set( 'domain', self::TEST_DOMAIN );
		self::$options->set( 'client_id', $client_id );
		self::$options->set( 'client_secret', $client_secret );
		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$options );

		$decoded_res = [];
		try {
			$api_client_creds->call();
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $decoded_res );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/oauth/token', $decoded_res['url'] );
		$this->assertEquals( 'POST', $decoded_res['method'] );
		$this->assertArrayHasKey( 'Content-Type', $decoded_res['headers'] );
		$this->assertEquals( 'application/json', $decoded_res['headers']['Content-Type'] );
		$this->assertArrayHasKey( 'client_id', $decoded_res['body'] );
		$this->assertEquals( $client_id, $decoded_res['body']['client_id'] );
		$this->assertArrayHasKey( 'client_secret', $decoded_res['body'] );
		$this->assertEquals( $client_secret, $decoded_res['body']['client_secret'] );
		$this->assertArrayHasKey( 'audience', $decoded_res['body'] );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/api/v2/', $decoded_res['body']['audience'] );
		$this->assertArrayHasKey( 'grant_type', $decoded_res['body'] );
		$this->assertEquals( 'client_credentials', $decoded_res['body']['grant_type'] );
	}

	/**
	 * Test a basic Client Credentials call against a mock API server.
	 */
	public function testCall() {
		$this->startHttpMocking();
		set_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME, uniqid() );

		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$options );

		// 1. Set the response to be a WP_Error, make sure we get null back, and check for a log entry.
		$this->http_request_type = 'wp_error';
		$this->assertNull( $api_client_creds->call() );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'Caught WP_Error.', $log[0]['message'] );

		// 2. Set the response to be an Auth0 server error, check for null, and check for another log entry.
		$this->http_request_type = 'auth0_api_error';
		$this->assertNull( $api_client_creds->call() );
		$log = self::$error_log->get();
		$this->assertCount( 2, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );

		// 3. Set the response to be successful but empty, check for null, and check for another log entry.
		$this->http_request_type = 'success_empty_body';
		$this->assertNull( $api_client_creds->call() );
		$log = self::$error_log->get();
		$this->assertCount( 3, $log );
		$this->assertEquals( 'No access_token returned.', $log[0]['message'] );

		// 4. Set the response to be successful but an invalid JWT, check for null, and check for another error entry.
		$this->http_request_type = 'access_token';
		$this->assertNull( $api_client_creds->call() );
		$log = self::$error_log->get();
		$this->assertCount( 4, $log );
		$this->assertEquals( 'Wrong number of segments', $log[0]['message'] );

		// Create a dummy decoded token.
		$dummy_decoded_token = (object) array( 'scope' => 'dummy:scope' );

		// Mock the parent decode_jwt method to return the dummy decoded token.
		$api_client_creds_mock = $this->getMockBuilder( WP_Auth0_Api_Client_Credentials::class )
			->setMethods( [ 'decode_jwt' ] )
			->setConstructorArgs( [ self::$options ] )
			->getMock();
		$api_client_creds_mock->method( 'decode_jwt' )
			->willReturn( $dummy_decoded_token );

		// Reflect the mocked class to make the get_token_decoded method public.
		$reflect_mock = new ReflectionClass( WP_Auth0_Api_Client_Credentials::class );
		$method       = $reflect_mock->getMethod( 'get_token_decoded' );
		$method->setAccessible( true );

		// 5. Make sure we get an access token back from the API call.
		$this->http_request_type = 'access_token';
		$this->assertEquals( '__test_access_token__', $api_client_creds_mock->call() );

		// 6. Make sure the dummy decoded token stored during handle_response is correct.
		$this->assertEquals( $dummy_decoded_token, $method->invoke( $api_client_creds_mock ) );
	}

	/**
	 * Specific mock API responses for this suite.
	 *
	 * @return array|null|WP_Error
	 */
	public function httpMock() {
		switch ( $this->getResponseType() ) {
			case 'access_token':
				return [
					'body'     => '{"access_token":"__test_access_token__"}',
					'response' => [ 'code' => 200 ],
				];
		}
		return $this->httpMockDefault();
	}

	/**
	 * Stop HTTP halting and mocking, reset JWKS transient.
	 */
	public function tearDown() {
		parent::tearDown();
		$this->stopHttpHalting();
		$this->stopHttpMocking();
		self::$error_log->clear();
		$this->assertEmpty( self::$error_log->get() );
		delete_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME );
	}
}
