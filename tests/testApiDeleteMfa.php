<?php
/**
 * Contains Class TestApiDeleteMfa.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestApiDeleteMfa.
 * Test the WP_Auth0_Api_Delete_User_Mfa class.
 */
class TestApiDeleteMfa extends TestCase {

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
		self::$options          = WP_Auth0_Options::Instance();
		self::$error_log        = new WP_Auth0_ErrorLog();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$options );
	}

	/**
	 * Test the request sent by the Client Credentials call.
	 */
	public function testRequest() {
		$this->startHttpHalting();
		self::$options->set( 'domain', self::TEST_DOMAIN );

		// Should fail with a missing user_id.
		$delete_mfa = $this->getStub( true );
		$returned   = $delete_mfa->call();
		$this->assertEquals( 0, $returned );

		// Should fail if not authorized to use the API.
		$delete_mfa = $this->getStub( false );
		$returned   = $delete_mfa->call( uniqid() );
		$this->assertEquals( 0, $returned );

		// Should succeed with a user_id + provider and set_bearer returning true.
		$delete_mfa  = $this->getStub( true );
		$decoded_res = [];
		try {
			$delete_mfa->call( 'test|1234567890', 'a-provider' );
		} catch ( Exception $e ) {
			$decoded_res = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $decoded_res );
		$this->assertEquals(
			'https://' . self::TEST_DOMAIN . '/api/v2/users/test%7C1234567890/multifactor/a-provider',
			$decoded_res['url']
		);
		$this->assertEquals( 'DELETE', $decoded_res['method'] );
		$this->assertEmpty( $decoded_res['body'] );
	}

	/**
	 * Test a basic Delete MFA call against a mock API server.
	 */
	public function testCall() {
		$this->startHttpMocking();
		self::$options->set( 'domain', self::TEST_DOMAIN );

		$delete_mfa = $this->getStub( true );

		// 1. Make sure that a transport returns the default failed response and logs an error.
		$this->http_request_type = 'wp_error';
		$this->assertEquals( 0, $delete_mfa->call( uniqid() ) );
		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'Caught WP_Error.', $log[0]['message'] );

		// 2. Make sure that an Auth0 API error returns the default failed response and logs an error.
		$this->http_request_type = 'auth0_api_error';
		$this->assertEquals( 0, $delete_mfa->call( uniqid() ) );
		$log = self::$error_log->get();
		$this->assertCount( 2, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );

		// 3. Make sure it succeeds.
		$this->http_request_type = 'success_delete_empty_body';
		$this->assertEquals( 1, $delete_mfa->call( uniqid() ) );
		$this->assertCount( 2, self::$error_log->get() );
	}

	/*
	 * PHPUnit overrides to run after tests.
	 */

	/**
	 * Stop HTTP halting and mocking, reset JWKS transient.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$options->set( 'domain', null );
		$this->stopHttpHalting();
		$this->stopHttpMocking();
		self::$error_log->clear();
		$this->assertEmpty( self::$error_log->get() );
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
			case 'success_delete_empty_body':
				return [
					'body'     => '',
					'response' => [ 'code' => 204 ],
				];
		}
		return $this->httpMockDefault();
	}

	/**
	 * Get a mocked WP_Auth0_Api_Delete_User_Mfa to return true or false for set_bearer.
	 *
	 * @param bool $set_bearer_returns - Should the set_bearer call succeed or fail.
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|WP_Auth0_Api_Delete_User_Mfa
	 */
	public function getStub( $set_bearer_returns ) {
		$mock = $this
			->getMockBuilder( WP_Auth0_Api_Delete_User_Mfa::class )
			->setMethods( [ 'set_bearer' ] )
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock->method( 'set_bearer' )->willReturn( $set_bearer_returns );
		return $mock;
	}
}
