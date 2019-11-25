<?php
/**
 * Contains Class TestApiGetJwks.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestApiGetJwks.
 * Test the WP_Auth0_Api_Get_Jwks class.
 */
class TestApiGetJwks extends WP_Auth0_Test_Case {

	use HttpHelpers;

	/**
	 * Test that the API request is made correctly.
	 */
	public function testThatApiRequestIsCorrect() {
		$this->startHttpHalting();

		self::$opts->set( 'domain', 'test.auth0.com' );
		$get_jwks_api = new WP_Auth0_Api_Get_Jwks( self::$opts );

		try {
			$http_data = [];
			$get_jwks_api->call();
		} catch ( Exception $e ) {
			$http_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test.auth0.com/.well-known/jwks.json', $http_data['url'] );
		$this->assertEquals(
			WP_Auth0_Api_Abstract::get_info_headers()['Auth0-Client'],
			$http_data['headers']['Auth0-Client']
		);
		$this->assertEquals( 'GET', $http_data['method'] );
	}

	/**
	 * Test that a network error (caught by WP and returned as a WP_Error) is handled properly.
	 */
	public function testThatNetworkErrorIsHandled() {
		$this->startHttpMocking();
		$this->http_request_type = 'wp_error';

		self::$opts->set( 'domain', 'test.auth0.com' );
		$get_jwks_api = new WP_Auth0_Api_Get_Jwks( self::$opts );

		$this->assertEquals( [], $get_jwks_api->call() );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'WP_Auth0_Api_Get_Jwks::call', $log[0]['section'] );
	}

	/**
	 * Test that a network error (caught by WP and returned as a WP_Error) is handled properly.
	 */
	public function testThatApiErrorIsHandled() {
		$this->startHttpMocking();
		$this->http_request_type = 'auth0_api_error';

		self::$opts->set( 'domain', 'test.auth0.com' );
		$get_jwks_api = new WP_Auth0_Api_Get_Jwks( self::$opts );

		$this->assertEquals( [], $get_jwks_api->call() );

		$log = self::$error_log->get();
		$this->assertCount( 1, $log );
		$this->assertEquals( 'WP_Auth0_Api_Get_Jwks::call', $log[0]['section'] );
	}

	/**
	 * Test that a network error (caught by WP and returned as a WP_Error) is handled properly.
	 */
	public function testThatSuccessfulCallIsReturned() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_jwks';

		self::$opts->set( 'domain', 'test.auth0.com' );
		$get_jwks_api = new WP_Auth0_Api_Get_Jwks( self::$opts );
		$jwks_result  = $get_jwks_api->call();

		$this->assertArrayHasKey( 'keys', $jwks_result );
		$this->assertCount( 1, $jwks_result['keys'] );
		$this->assertArrayHasKey( 'x5c', $jwks_result['keys'][0] );
		$this->assertArrayHasKey( 'kid', $jwks_result['keys'][0] );
		$this->assertEquals( '__test_kid_1__', $jwks_result['keys'][0]['kid'] );
	}
}
