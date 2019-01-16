<?php
/**
 * Contains Class TestApiJobsVerification.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestApiJobsVerification.
 * Test the WP_Auth0_Api_Jobs_Verification class.
 */
class TestApiJobsVerification extends WP_Auth0_Test_Case {

	use httpHelpers {
		httpMock as protected httpMockDefault;
	}

	/**
	 * Test user_id.
	 */
	const TEST_USER_ID = 'test|1234567890';

	/**
	 * Test Client ID.
	 */
	const TEST_CLIENT_ID = '__test_client_id__';

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	protected static $api_client_creds;

	/**
	 * Set up before test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );
	}

	/**
	 * Test the request sent by the Client Credentials call.
	 */
	public function testRequest() {
		$this->startHttpHalting();
		self::$opts->set( 'domain', self::TEST_DOMAIN );
		self::$opts->set( 'client_id', self::TEST_CLIENT_ID );

		// Should fail without a user_id.
		$jobs_verification = $this->getStub( true );
		$returned          = $jobs_verification->call();
		$this->assertFalse( $returned );

		// Should fail if not authorized to use the API.
		$jobs_verification = $this->getStub( false );
		$returned          = $jobs_verification->call( self::TEST_USER_ID );
		$this->assertFalse( $returned );

		// Should succeed with a user_id + provider and set_bearer returning true.
		$jobs_verification = $this->getStub( true );
		$decoded_res       = [];
		try {
			$jobs_verification->call( self::TEST_USER_ID );
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $decoded_res );
		$this->assertEquals( 'https://' . self::TEST_DOMAIN . '/api/v2/jobs/verification-email', $decoded_res['url'] );
		$this->assertEquals( 'POST', $decoded_res['method'] );
		$this->assertArrayHasKey( 'user_id', $decoded_res['body'] );
		$this->assertEquals( self::TEST_USER_ID, $decoded_res['body']['user_id'] );
		$this->assertArrayHasKey( 'client_id', $decoded_res['body'] );
		$this->assertEquals( self::TEST_CLIENT_ID, $decoded_res['body']['client_id'] );
	}

	/**
	 * Test a basic Delete MFA call against a mock API server.
	 */
	public function testCall() {
		$this->startHttpMocking();
		self::$opts->set( 'domain', self::TEST_DOMAIN );
		self::$opts->set( 'client_id', self::TEST_CLIENT_ID );

		$jobs_verification = $this->getStub( true );

		// 1. Make sure that a transport returns the default failed response and logs an error.
		$this->http_request_type = 'wp_error';
		$this->assertFalse( $jobs_verification->call( self::TEST_USER_ID ) );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'Caught WP_Error.', $log[0]['message'] );

		// 2. Make sure that an Auth0 API error returns the default failed response and logs an error.
		$this->http_request_type = 'auth0_api_error';
		$this->assertFalse( $jobs_verification->call( self::TEST_USER_ID ) );
		$log = self::$error_log->get();
		$this->assertCount( 2, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );

		// 3. Make sure it succeeds.
		$this->http_request_type = 'success_job_email_verification';
		$this->assertTrue( $jobs_verification->call( self::TEST_USER_ID ) );
		$this->assertCount( 2, self::$error_log->get() );
	}

	/*
	 * Test helper functions.
	 */

	/**
	 * Specific mock API responses for this suite.
	 *
	 * @return array|null|WP_Error
	 */
	public function httpMock() {
		switch ( $this->getResponseType() ) {
			case 'success_job_email_verification':
				return [
					'body'     => json_encode(
						[
							'type'       => 'verification_email',
							'status'     => 'pending',
							'created_at' => date( 'c' ),
							'id'         => 'job_' . uniqid(),
						]
					),
					'response' => [ 'code' => 201 ],
				];
		}
		return $this->httpMockDefault();
	}

	/**
	 * Get a mocked WP_Auth0_Api_Jobs_Verification to return true or false for set_bearer.
	 *
	 * @param bool $set_bearer_returns - Should the set_bearer call succeed or fail.
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|WP_Auth0_Api_Jobs_Verification
	 */
	public function getStub( $set_bearer_returns ) {
		$mock = $this
			->getMockBuilder( WP_Auth0_Api_Jobs_Verification::class )
			->setMethods( [ 'set_bearer' ] )
			->setConstructorArgs( [ self::$opts, self::$api_client_creds, self::TEST_USER_ID ] )
			->getMock();
		$mock->method( 'set_bearer' )->willReturn( $set_bearer_returns );
		return $mock;
	}
}
