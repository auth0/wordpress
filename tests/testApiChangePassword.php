<?php
/**
 * Contains Class TestApiChangePassword.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestApiChangePassword.
 * Test the WP_Auth0_Api_Change_Password class.
 */
class TestApiChangePassword extends WP_Auth0_Test_Case {

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
	 * Set up before test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );
	}

	/**
	 * Test the request sent by the change password call.
	 */
	public function testRequest() {
		$this->startHttpHalting();
		self::$opts->set( 'domain', self::TEST_DOMAIN );

		// Mock for a successful API call.
		$change_password = $this->getStub( true );

		// Should fail with a missing user_id and password.
		$returned = $change_password->call();
		$this->assertFalse( $returned );

		// Should fail with a missing password.
		$returned = $change_password->call( uniqid() );
		$this->assertFalse( $returned );

		// Should fail if not authorized to use the API.
		$change_password = $this->getStub( false );
		$returned        = $change_password->call( uniqid(), uniqid() );
		$this->assertFalse( $returned );

		// Should succeed with a user_id + provider and set_bearer returning true.
		$change_password = $this->getStub( true );
		$decoded_res     = [];
		try {
			$change_password->call( 'test|1234567890', 'strong-password' );
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $decoded_res );
		$this->assertEquals(
			'https://' . self::TEST_DOMAIN . '/api/v2/users/test%7C1234567890',
			$decoded_res['url']
		);
		$this->assertEquals( 'PATCH', $decoded_res['method'] );
		$this->assertArrayHasKey( 'password', $decoded_res['body'] );
		$this->assertEquals( 'strong-password', $decoded_res['body']['password'] );
	}

	/**
	 * Test a basic change password call against a mock API server.
	 */
	public function testCall() {
		$this->startHttpMocking();
		self::$opts->set( 'domain', self::TEST_DOMAIN );

		// Mock for a successful API call.
		$change_password = $this->getStub( true );

		// 1. Make sure that a transport returns the default failed response and logs an error.
		$this->http_request_type = 'wp_error';
		$this->assertFalse( $change_password->call( uniqid(), uniqid() ) );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'Caught WP_Error.', $log[0]['message'] );

		// 2. Make sure that an Auth0 API error returns the default failed response and logs an error.
		$this->http_request_type = 'auth0_api_error';
		$this->assertFalse( $change_password->call( uniqid(), uniqid() ) );
		$log = self::$error_log->get();
		$this->assertCount( 2, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );

		// 3. Make sure that a weak password error returns the correct message.
		$this->http_request_type = 'failed_weak_password';
		$this->assertEquals(
			'Password is too weak, please choose a different one.',
			$change_password->call( uniqid(), uniqid() )
		);
		$log = self::$error_log->get();
		$this->assertCount( 3, $log );
		$this->assertEquals( '400', $log[0]['code'] );

		// 4. Make sure it succeeds.
		$this->http_request_type = 'success_empty_body';
		$this->assertTrue( $change_password->call( uniqid(), uniqid() ) );
		$this->assertCount( 3, self::$error_log->get() );
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
			case 'failed_weak_password':
				return [
					'body'     => json_encode(
						[
							'statusCode' => 400,
							'error'      => 'Bad Request',
							'message'    => 'PasswordStrengthError: Password is too weak',
						]
					),
					'response' => [ 'code' => 400 ],
				];
		}
		return $this->httpMockDefault();
	}

	/**
	 * Get a mocked WP_Auth0_Api_Change_Password to return true or false for set_bearer.
	 *
	 * @param bool $set_bearer_returns - Should the set_bearer call succeed or fail.
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|WP_Auth0_Api_Change_Password
	 */
	public function getStub( $set_bearer_returns ) {
		$mock = $this
			->getMockBuilder( WP_Auth0_Api_Change_Password::class )
			->setMethods( [ 'set_bearer' ] )
			->setConstructorArgs( [ self::$opts, self::$api_client_creds ] )
			->getMock();
		$mock->method( 'set_bearer' )->willReturn( $set_bearer_returns );
		return $mock;
	}
}
