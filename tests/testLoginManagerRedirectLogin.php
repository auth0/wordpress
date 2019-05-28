<?php
/**
 * Contains Class TestLoginManagerRedirectLogin.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestLoginManagerRedirectLogin.
 * Test the WP_Auth0_LoginManager::init_auth0() method.
 */
class TestLoginManagerRedirectLogin extends WP_Auth0_Test_Case {

	use HttpHelpers {
		httpMock as protected httpMockDefault;
	}

	use RedirectHelpers;

	use UsersHelper;

	/**
	 * WP_Auth0_LoginManager instance to test.
	 *
	 * @var WP_Auth0_LoginManager
	 */
	protected $login;

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();
		$this->login = new WP_Auth0_LoginManager( new WP_Auth0_UsersRepo( self::$opts ), self::$opts );

		self::$opts->set( 'requires_verified_email', 0 );

		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$users_repo       = self::$users_repo; // PHP 5.6.
		$users_repo::update_meta( 1, 'auth0_id', 'auth0|1234567890' );

		add_filter( 'auth0_get_wp_user', [ $this, 'auth0_get_wp_user_handler' ], 1, 2 );

		set_transient( WP_Auth0_Api_Client_Credentials::TOKEN_TRANSIENT_KEY, '__test_access_token__', 9999 );
		set_transient( WP_Auth0_Api_Client_Credentials::SCOPE_TRANSIENT_KEY, 'read:users', 9990 );
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();
		remove_filter( 'auth0_get_wp_user', [ $this, 'auth0_get_wp_user_handler' ], 1 );

		delete_transient( WP_Auth0_Api_Client_Credentials::TOKEN_TRANSIENT_KEY );
		delete_transient( WP_Auth0_Api_Client_Credentials::SCOPE_TRANSIENT_KEY );

		remove_filter( 'auth0_use_management_api_for_userinfo', '__return_false', 10 );
	}

	/**
	 * Stop the WP_Auth0_LoginManager::login_user() call with user info gathered.
	 *
	 * @param null|WP_user $user - WP_User, if one was found.
	 * @param stdClass     $userinfo - Userinfo from Auth0.
	 *
	 * @throws Exception - Always.
	 */
	public function auth0_get_wp_user_handler( $user, $userinfo ) {
		throw new Exception(
			serialize(
				[
					'user'     => $user,
					'userinfo' => $userinfo,
				]
			)
		);
	}

	/**
	 * Mock responses for this test suite only.
	 *
	 * @param string|null $response_type - HTTP response type to use.
	 * @param array|null  $args - HTTP args.
	 * @param string|null $url - Remote URL.
	 *
	 * @return array|WP_Error
	 *
	 * @throws Exception - If set to halt on response.
	 */
	public function httpMock( $response_type = null, array $args = null, $url = null ) {
		$response_type = $response_type ?: $this->getResponseType();
		switch ( $response_type ) {
			case 'success_exchange_code_valid_id_token':
				$id_token_payload = [
					'sub' => '__test_id_token_sub__',
					'iss' => 'https://test.auth0.com/',
					'aud' => '__test_client_id__',
				];
				$id_token         = JWT::encode( $id_token_payload, '__test_client_secret__' );
				return [
					'body'     => sprintf(
						'{"access_token":"__test_access_token__","id_token":"%s"}',
						$id_token
					),
					'response' => [ 'code' => 200 ],
				];
		}
		return $this->httpMockDefault( $response_type, $args, $url );
	}

	/**
	 * Test that plugin not halts login process.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Should not be encountered during this test.
	 * @throws WP_Auth0_InvalidIdTokenException - Should not be encountered during this test.
	 */
	public function testThatInvalidConfigurationHaltsLogin() {
		$_REQUEST['code'] = uniqid();

		try {
			$this->login->redirect_login();
			$caught_exception = false;
		} catch ( WP_Auth0_LoginFlowValidationException $e ) {
			$caught_exception = ( 'Error exchanging code' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that an empty code parameter halts login process.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Should not be encountered during this test.
	 * @throws WP_Auth0_InvalidIdTokenException - Should not be encountered during this test.
	 */
	public function testThatMissingCodeHaltsLogin() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_code_exchange';

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );

		try {
			$this->login->redirect_login();
			$caught_exception = false;
		} catch ( WP_Auth0_LoginFlowValidationException $e ) {
			$caught_exception = ( 'Error exchanging code' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that an unknown network error halts the login process.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Should not be encountered during this test.
	 * @throws WP_Auth0_InvalidIdTokenException - Should not be encountered during this test.
	 */
	public function testThatNetworkErrorHaltsLogin() {
		$this->startHttpMocking();
		$this->http_request_type = 'wp_error';

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		$_REQUEST['code'] = uniqid();

		try {
			$this->login->redirect_login();
			$caught_exception = false;
		} catch ( WP_Auth0_LoginFlowValidationException $e ) {
			$caught_exception = ( 'Error exchanging code' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that an unknown API error halts the login process.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Should not be encountered during this test.
	 * @throws WP_Auth0_InvalidIdTokenException - Should not be encountered during this test.
	 */
	public function testThatApiErrorHaltsLogin() {
		$this->startHttpMocking();
		$this->http_request_type = 'auth0_api_error';

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		$_REQUEST['code'] = uniqid();

		try {
			$this->login->redirect_login();
			$caught_exception = false;
		} catch ( WP_Auth0_LoginFlowValidationException $e ) {
			$caught_exception = ( 'Error exchanging code' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that an access denied response when exchanging tokens triggers special error handling.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Should not be encountered during this test.
	 * @throws WP_Auth0_InvalidIdTokenException - Should not be encountered during this test.
	 */
	public function testThatAccessDeniedLogsCorrectError() {
		$this->startHttpMocking();
		$this->http_request_type = 'auth0_access_denied';

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		$_REQUEST['code'] = uniqid();

		try {
			$caught_exception = false;
			$this->login->redirect_login();
		} catch ( WP_Auth0_LoginFlowValidationException $e ) {
			$caught_exception = ( 'Error exchanging code' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );

		$error_log = self::$error_log->get();
		$this->assertCount( 1, $error_log );
		$this->assertContains( 'WP_Auth0_Api_Exchange_Code::handle_response', $error_log[0]['section'] );
		$this->assertContains( 'Please check the Client Secret', $error_log[0]['message'] );
	}

	/**
	 * Test that the exchange code call is formatted properly.
	 */
	public function testThatExchangeTokenCallIsCorrect() {
		$this->startHttpHalting();

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		$_REQUEST['code'] = uniqid();

		try {
			$http_data = [];
			$this->login->redirect_login();
		} catch ( Exception $e ) {
			$http_data = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $http_data );
		$this->assertEquals( 'https://test.auth0.com/oauth/token', $http_data['url'] );
		$this->assertEquals( site_url( '/index.php?auth0=1' ), $http_data['body']['redirect_uri'] );
		$this->assertEquals( $_REQUEST['code'], $http_data['body']['code'] );
		$this->assertEquals( '__test_client_id__', $http_data['body']['client_id'] );
		$this->assertEquals( '__test_client_secret__', $http_data['body']['client_secret'] );
		$this->assertEquals( 'authorization_code', $http_data['body']['grant_type'] );
	}

	/**
	 * Test that an invalid ID token halts the login process.
	 *
	 * @throws WP_Auth0_BeforeLoginException - Should not be encountered during this test.
	 * @throws WP_Auth0_LoginFlowValidationException - Should not be encountered during this test.
	 */
	public function testThatInvalidIdTokenHaltsLogin() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_code_exchange';

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		$_REQUEST['code'] = uniqid();

		try {
			$caught_exception = false;
			$this->login->redirect_login();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_exception = ( 'Wrong number of segments' === $e->getMessage() );
		}

		$this->assertTrue( $caught_exception );
	}

	/**
	 * Test that the user information is retrieved via the Management API.
	 */
	public function testThatGetUserCallIsCorrect() {
		$this->startHttpMocking();
		$this->http_request_type = [
			// Mocked successful code exchange with a valid ID token.
			'success_exchange_code_valid_id_token',
			// Stop the get user call.
			'halt',
		];

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		$_REQUEST['code'] = uniqid();

		try {
			$http_data = [];
			$this->login->redirect_login();
		} catch ( Exception $e ) {
			$http_data = unserialize( $e->getMessage() );
		}

		$this->assertNotEmpty( $http_data );
		$this->assertEquals( 'https://test.auth0.com/api/v2/users/__test_id_token_sub__', $http_data['url'] );
		$this->assertEquals( 'Bearer __test_access_token__', $http_data['headers']['Authorization'] );
	}

	/**
	 * Test that the user information is retrieved via the Management API by default.
	 */
	public function testThatLoginUserIsCalledWithManagementApiUserinfo() {
		$this->startHttpMocking();
		$this->http_request_type = [
			// Mocked successful code exchange with a valid ID token.
			'success_exchange_code_valid_id_token',
			// Mocked successful get user call.
			'success_get_user',
		];

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		$_REQUEST['code'] = uniqid();

		try {
			$user_data = [];
			$this->login->redirect_login();
		} catch ( Exception $e ) {
			$user_data = unserialize( $e->getMessage() );
		}

		$this->assertTrue( $user_data['user'] instanceof WP_User );
		$this->assertEquals( 1, $user_data['user']->ID );
		$this->assertEquals( 'auth0|1234567890', $user_data['userinfo']->user_id );
		$this->assertEquals( 'auth0|1234567890', $user_data['userinfo']->sub );
		$this->assertNotEmpty( $user_data['userinfo']->user_metadata );
		$this->assertNotEmpty( $user_data['userinfo']->app_metadata );
	}

	/**
	 * Test that the user information is from the ID token if the Management API fails.
	 */
	public function testThatLoginUserIsCalledWithIdTokenIfNoApiAccess() {
		$this->startHttpMocking();
		$this->http_request_type = [
			// Mocked successful code exchange with a valid ID token.
			'success_exchange_code_valid_id_token',
			// Mocked failed get user call.
			'auth0_api_error',
		];

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		$_REQUEST['code'] = uniqid();

		try {
			$user_data = [];
			$this->login->redirect_login();
		} catch ( Exception $e ) {
			$user_data = unserialize( $e->getMessage() );
		}

		$this->assertEmpty( $user_data['user'] );
		$this->assertEquals( '__test_id_token_sub__', $user_data['userinfo']->user_id );
		$this->assertEquals( '__test_id_token_sub__', $user_data['userinfo']->sub );
	}

	/**
	 * Test that the user information is from the ID token if migrations are being used.
	 */
	public function testThatLoginUserIsCalledWithIdTokenIfFilterIsSetToFalse() {
		$this->startHttpMocking();
		$this->http_request_type = [
			// Mocked successful code exchange with a valid ID token.
			'success_exchange_code_valid_id_token',
			// Mocked successful get user call.
			'success_get_user',
		];

		self::$opts->set( 'domain', 'test.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		add_filter( 'auth0_use_management_api_for_userinfo', '__return_false', 10 );
		$_REQUEST['code'] = uniqid();

		try {
			$user_data = [];
			$this->login->redirect_login();
		} catch ( Exception $e ) {
			$user_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals( '__test_id_token_sub__', $user_data['userinfo']->user_id );
	}
}
