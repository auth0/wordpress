<?php
/**
 * Contains Class TestApiClientCredentials.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

class ApiClientCredentialsTest extends WP_Auth0_Test_Case {

	use HttpHelpers;

	/**
	 * Run after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		delete_transient( 'auth0_api_token' );
		delete_transient( 'auth0_api_token_scope' );
	}

	/**
	 * Test the request sent by the Client Credentials call.
	 */
	public function testRequest() {
		$this->startHttpHalting();

		$client_id     = uniqid();
		$client_secret = uniqid();

		self::$opts->set( 'domain', self::TEST_DOMAIN );
		self::$opts->set( 'client_id', $client_id );
		self::$opts->set( 'client_secret', $client_secret );
		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );

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
	 * Test that a WP error (HTTP not successful) is logged and stored token data is cleared.
	 */
	public function testThatWpErrorReturnsNull() {
		$this->startHttpMocking();
		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );

		$this->http_request_type = 'wp_error';
		$this->assertNull( $api_client_creds->call() );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'Caught WP_Error.', $log[0]['message'] );
	}

	/**
	 * Test that an API error is logged and stored token data is cleared.
	 */
	public function testThatApiErrorReturnsNull() {
		$this->startHttpMocking();
		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );

		$this->http_request_type = 'auth0_api_error';
		$this->assertNull( $api_client_creds->call() );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'caught_api_error', $log[0]['code'] );
	}

	/**
	 * Test that an empty access token is logged and stored token data is cleared.
	 */
	public function testThatEmptyAccessTokenReturnsNull() {
		$this->startHttpMocking();
		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );

		$this->http_request_type = 'success_empty_body';
		$this->assertNull( $api_client_creds->call() );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'No access_token returned.', $log[0]['message'] );
	}

	/**
	 * Test that a returned access token is returned and stored.
	 */
	public function testThatSuccessfulCallStoresTokenAndScope() {
		$this->startHttpMocking();
		$api_client_creds = new WP_Auth0_Api_Client_Credentials( self::$opts );

		$this->http_request_type = 'success_access_token';
		$timeout                 = time() + 1000;
		$this->assertEquals( '__test_access_token__', $api_client_creds->call() );
		$this->assertEquals( '__test_access_token__', get_transient( 'auth0_api_token' ) );
		$this->assertEquals( 'update:users read:users', get_transient( 'auth0_api_token_scope' ) );
		$this->assertLessThan( $timeout, (int) get_transient( '_transient_timeout_auth0_api_token_scope' ) );
		$this->assertLessThan( $timeout, (int) get_transient( '_transient_timeout_auth0_api_token' ) );
		$log = self::$error_log->get();
		$this->assertCount( 0, $log );
	}

	/**
	 * Test that the stored token is returned, if present.
	 */
	public function testThatGetStoredTokenReturnsCorrectly() {
		$this->assertFalse( WP_Auth0_Api_Client_Credentials::get_stored_token() );

		$token = uniqid();
		set_transient( 'auth0_api_token', $token );
		$this->assertEquals( $token, WP_Auth0_Api_Client_Credentials::get_stored_token() );
	}

	/**
	 * Test that the stored token data is deleted.
	 */
	public function testThatDeleteStoreRemovesTokenData() {
		$token = uniqid();
		set_transient( 'auth0_api_token', $token );
		$this->assertEquals( $token, get_transient( 'auth0_api_token' ) );

		$scope = uniqid();
		set_transient( 'auth0_api_token_scope', $scope );
		$this->assertEquals( $scope, get_transient( 'auth0_api_token_scope' ) );

		WP_Auth0_Api_Client_Credentials::delete_store();

		$this->assertFalse( get_transient( 'auth0_api_token' ) );
		$this->assertFalse( get_transient( 'auth0_api_token_scope' ) );
	}

	/**
	 * Test that the stored scope data is checked against a passed-in value.
	 */
	public function testThatCheckStoredScopeChecksScopeCorrectly() {
		$this->assertFalse( WP_Auth0_Api_Client_Credentials::check_stored_scope( 'scope:none' ) );

		set_transient( 'auth0_api_token_scope', 'scope:one' );
		$this->assertTrue( WP_Auth0_Api_Client_Credentials::check_stored_scope( 'scope:one' ) );
		$this->assertFalse( WP_Auth0_Api_Client_Credentials::check_stored_scope( 'scope:two' ) );

		set_transient( 'auth0_api_token_scope', 'scope:one scope:two' );
		$this->assertTrue( WP_Auth0_Api_Client_Credentials::check_stored_scope( 'scope:one' ) );
		$this->assertTrue( WP_Auth0_Api_Client_Credentials::check_stored_scope( 'scope:two' ) );
		$this->assertFalse( WP_Auth0_Api_Client_Credentials::check_stored_scope( 'scope:three' ) );
	}
}
