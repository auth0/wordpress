<?php
/**
 * Contains Class TestInitialSetupConsentExchangeCode.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

class InitialSetupConsentExchangeCodeTest extends WP_Auth0_Test_Case {

	use HttpHelpers;

	/**
	 * Test that a missing code parameter will return null.
	 */
	public function testThatMissingCodeReturnsNull() {
		$setup_consent = new WP_Auth0_InitialSetup_Consent( self::$opts );
		$this->assertNull( $setup_consent->exchange_code() );
	}

	/**
	 * Test that the exchange code call is formatted properly.
	 */
	public function testThatExchangeTokenCallIsCorrect() {
		$this->startHttpHalting();

		$_REQUEST['code'] = uniqid();

		$setup_consent = new WP_Auth0_InitialSetup_Consent( self::$opts );

		try {
			$http_data = [];
			$setup_consent->exchange_code();
		} catch ( Exception $e ) {
			$http_data = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $http_data );
		$this->assertEquals( 'https://auth0.auth0.com/oauth/token', $http_data['url'] );
		$this->assertEquals(
			'http://example.org/wp-admin/admin.php?page=wpa0-setup&callback=1',
			$http_data['body']['redirect_uri']
		);
		$this->assertEquals( $_REQUEST['code'], $http_data['body']['code'] );
		$this->assertEquals( 'http://example.org', $http_data['body']['client_id'] );
		$this->assertEmpty( $http_data['body']['client_secret'] );
		$this->assertEquals( 'authorization_code', $http_data['body']['grant_type'] );
	}

	/**
	 * Test that an API failure will return null.
	 */
	public function testThatFailedApiCallReturnsNull() {
		$this->startHttpMocking();
		$this->http_request_type = 'wp_error';

		$_REQUEST['code'] = uniqid();

		$setup_consent = new WP_Auth0_InitialSetup_Consent( self::$opts );
		$this->assertNull( $setup_consent->exchange_code() );
	}

	/**
	 * Test that a successful call results in an access token being returned.
	 */
	public function testThatExchangeCodeReturnsAccessToken() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_code_exchange';

		$_REQUEST['code'] = uniqid();

		$setup_consent = new WP_Auth0_InitialSetup_Consent( self::$opts );
		$this->assertEquals( '__test_access_token__', $setup_consent->exchange_code() );
	}

	/**
	 * Test that there are no errors generated and null is returned for a malformed success response.
	 */
	public function testThatExchangeCodeReturnsNullIfNoAccessToken() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_get_user';

		$_REQUEST['code'] = uniqid();

		$setup_consent = new WP_Auth0_InitialSetup_Consent( self::$opts );
		$this->assertNull( $setup_consent->exchange_code() );
	}
}
