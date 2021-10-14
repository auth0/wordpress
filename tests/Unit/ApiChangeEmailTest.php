<?php
/**
 * Contains Class TestApiChangeEmail.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

class ApiChangeEmailTest extends WP_Auth0_Test_Case {

	use HttpHelpers {
		httpMock as protected httpMockDefault;
	}

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	protected static $api_client_creds;

	/**
	 * Run before the test suite.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );
	}

	/**
	 * Runs after each test completes.
	 */
	public function tearDown(): void {
		parent::tearDown();
		delete_transient( 'auth0_api_token' );
		delete_transient( 'auth0_api_token_scope' );
	}

	/**
	 * Test that empty parameters will return false.
	 */
	public function testThatEmptyUserParamsReturnsFalse() {
		$change_email = $this->getStub( true );

		// Should fail with a missing user_id and email.
		$this->assertFalse( $change_email->call() );

		// Should fail with a missing email.
		$this->assertFalse( $change_email->call( uniqid() ) );
	}

	/**
	 * Test that a failed Client Credentials grant will return false.
	 */
	public function testThatFailedApiReturnsFalse() {
		$change_email = $this->getStub( false );
		$this->assertFalse( $change_email->call( uniqid(), uniqid() ) );
	}

	/**
	 * Test the request sent by the change email call.
	 */
	public function testThatApiCallIsFormedCorrectly() {
		$this->startHttpHalting();
		self::$opts->set( 'domain', self::TEST_DOMAIN );
		self::$opts->set( 'client_id', '__test_client_id__' );

		// Should succeed with a user_id + provider and set_bearer returning true.
		$change_email = $this->getStub( true );
		$decoded_res  = [];
		try {
			$change_email->call( 'test|1234567890', 'email@address.com' );
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $decoded_res );
		$this->assertEquals(
			'https://' . self::TEST_DOMAIN . '/api/v2/users/test%7C1234567890',
			$decoded_res['url']
		);
		$this->assertEquals( 'PATCH', $decoded_res['method'] );
		$this->assertArrayHasKey( 'email', $decoded_res['body'] );
		$this->assertEquals( 'email@address.com', $decoded_res['body']['email'] );
		$this->assertArrayHasKey( 'email_verified', $decoded_res['body'] );
		$this->assertTrue( $decoded_res['body']['email_verified'] );
		$this->assertArrayHasKey( 'client_id', $decoded_res['body'] );
		$this->assertEquals( '__test_client_id__', $decoded_res['body']['client_id'] );
	}

	/**
	 * Make sure that a transport error returns the default failed response and logs an error.
	 */
	public function testThatWpErrorIsHandledProperly() {
		$this->startHttpMocking();
		self::$opts->set( 'domain', self::TEST_DOMAIN );

		// Mock for a successful API call.
		$change_email = $this->getStub( true );

		$this->http_request_type = 'wp_error';
		$this->assertFalse( $change_email->call( uniqid(), uniqid() ) );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'Caught WP_Error.', $log[0]['message'] );
	}

	/**
	 * Make sure that an Auth0 API error returns the default failed response and logs an error.
	 */
	public function testThatApiErrorIsHandledProperly() {
		$this->startHttpMocking();
		self::$opts->set( 'domain', self::TEST_DOMAIN );

		// Mock for a successful API call.
		$change_email = $this->getStub( true );

		$this->http_request_type = 'auth0_api_error';
		$this->assertFalse( $change_email->call( uniqid(), uniqid() ) );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );
	}

	/**
	 * Test a successful API call.
	 */
	public function testThatSuccessfulApiCallReturnsTrue() {
		$this->startHttpMocking();
		self::$opts->set( 'domain', self::TEST_DOMAIN );

		// Mock for a successful API call.
		$change_email = $this->getStub( true );

		$this->http_request_type = 'success_empty_body';
		$this->assertTrue( $change_email->call( uniqid(), uniqid() ) );
		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * Test that the API call succeeds if there is a token stored with the correct scope.
	 */
	public function testThatApiCallSucceedsWithStoredToken() {
		$this->startHttpMocking();

		set_transient( 'auth0_api_token', uniqid() );
		set_transient( 'auth0_api_token_scope', 'update:users' );

		$this->http_request_type = 'success_empty_body';
		$api                     = new WP_Auth0_Api_Change_Email( self::$opts, self::$api_client_creds );
		$this->assertTrue( $api->call( uniqid(), uniqid() ) );
	}

	/**
	 * Test that the API call fails if there is a token stored with insufficient scope.
	 */
	public function testThatApiCallFailsWithInsufficientScope() {
		$this->startHttpMocking();

		set_transient( 'auth0_api_token', uniqid() );
		set_transient( 'auth0_api_token_scope', 'read:users' );

		$api = new WP_Auth0_Api_Change_Email( self::$opts, self::$api_client_creds );
		$this->assertFalse( $api->call( uniqid(), uniqid() ) );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'insufficient_scope', $log[0]['code'] );
	}

	/**
	 * Test that set bearer fails if there is no stored token and a CC grant fails.
	 */
	public function testThatSetBearerFailsWhenCannotGetToken() {
		$this->startHttpMocking();

		$this->http_request_type = [ 'wp_error', 'success_empty_body' ];
		$api                     = new WP_Auth0_Api_Change_Email( self::$opts, self::$api_client_creds );
		$this->assertFalse( $api->call( uniqid(), uniqid() ) );

		$this->assertFalse( get_transient( 'auth0_api_token' ) );
		$this->assertFalse( get_transient( 'auth0_api_token_scope' ) );
	}

	/**
	 * Test that set bearer succeeds if a token with the correct scope can be retrieved.
	 */
	public function testThatSetBearerSucceedsWhenCanGetToken() {
		$this->startHttpMocking();

		$this->http_request_type = [ 'success_access_token', 'success_empty_body' ];
		$api                     = new WP_Auth0_Api_Change_Email( self::$opts, self::$api_client_creds );
		$this->assertTrue( $api->call( uniqid(), uniqid() ) );

		$this->assertEquals( '__test_access_token__', get_transient( 'auth0_api_token' ) );
		$this->assertEquals( 'update:users read:users', get_transient( 'auth0_api_token_scope' ) );
	}

	/*
	 * Test helper functions.
	 */

	/**
	 * Get a mocked WP_Auth0_Api_Change_Email to return true or false for set_bearer.
	 *
	 * @param bool $set_bearer_returns - Should the set_bearer call succeed or fail.
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|WP_Auth0_Api_Change_Email
	 */
	public function getStub( $set_bearer_returns ) {
		$mock = $this
			->getMockBuilder( WP_Auth0_Api_Change_Email::class )
			->setMethods( [ 'set_bearer' ] )
			->setConstructorArgs( [ self::$opts, self::$api_client_creds ] )
			->getMock();
		$mock->method( 'set_bearer' )->willReturn( $set_bearer_returns );
		return $mock;
	}
}
