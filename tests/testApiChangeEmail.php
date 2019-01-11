<?php
/**
 * Contains Class TestApiChangeEmail.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestApiChangeEmail.
 * Test the WP_Auth0_Api_Change_Email class.
 */
class TestApiChangeEmail extends TestCase {

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
	 * Run before the test suite.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$options          = WP_Auth0_Options::Instance();
		self::$error_log        = new WP_Auth0_ErrorLog();
		self::$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$options );
	}

	/**
	 * Run after each test.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$options->set( 'domain', null );
		$this->stopHttpHalting();
		$this->stopHttpMocking();
		self::$error_log->clear();
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
		self::$options->set( 'domain', self::TEST_DOMAIN );

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
		$this->assertTrue( $decoded_res['body']['email_verified'] );
	}

	/**
	 * Make sure that a transport error returns the default failed response and logs an error.
	 */
	public function testThatWpErrorIsHandledProperly() {
		$this->startHttpMocking();
		self::$options->set( 'domain', self::TEST_DOMAIN );

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
		self::$options->set( 'domain', self::TEST_DOMAIN );

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
		self::$options->set( 'domain', self::TEST_DOMAIN );

		// Mock for a successful API call.
		$change_email = $this->getStub( true );

		$this->http_request_type = 'success_empty_body';
		$this->assertTrue( $change_email->call( uniqid(), uniqid() ) );
		$this->assertEmpty( self::$error_log->get() );
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
			->setConstructorArgs( [ self::$options, self::$api_client_creds ] )
			->getMock();
		$mock->method( 'set_bearer' )->willReturn( $set_bearer_returns );
		return $mock;
	}
}
