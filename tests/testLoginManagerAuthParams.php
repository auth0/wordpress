<?php
/**
 * Contains Class TestLoginManagerAuthParams.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestLoginManagerAuthParams.
 * Tests that the WP_Auth0_LoginManager:get_authorize_params() method functions as expected.
 */
class TestLoginManagerAuthParams extends WP_Auth0_Test_Case {

	public function testThatDefaultAuthParamsAreCorrect() {
		self::$opts->set( 'client_id', '__test_client_id_1__' );
		$auth_params = WP_Auth0_LoginManager::get_authorize_params();

		$this->assertEquals( '__test_client_id_1__', $auth_params['client_id'] );
		$this->assertEquals( 'openid email profile', $auth_params['scope'] );
		$this->assertEquals( 'code', $auth_params['response_type'] );
		$this->assertEquals( 'query', $auth_params['response_mode'] );
		$this->assertEquals( WP_Auth0_Nonce_Handler::get_instance()->get_unique(), $auth_params['nonce'] );
		$this->assertEquals( site_url( 'index.php?auth0=1' ), $auth_params['redirect_uri'] );
		$this->assertArrayNotHasKey( 'auth0Client', $auth_params );

		$decoded_state = json_decode( base64_decode( $auth_params['state'] ) );

		$this->assertFalse( $decoded_state->interim );
		$this->assertEquals( WP_Auth0_State_Handler::get_instance()->get_unique(), $decoded_state->nonce );
		$this->assertEquals( 'http://example.org', $decoded_state->redirect_to );
	}

	public function testThatRedirectInGetAppearsInState() {
		$_GET['redirect_to'] = 'http://example.org/get-redirect';
		$auth_params         = WP_Auth0_LoginManager::get_authorize_params();
		$decoded_state       = json_decode( base64_decode( $auth_params['state'] ) );
		$this->assertEquals( 'http://example.org/get-redirect', $decoded_state->redirect_to );
	}

	public function testThatConnectionPassedAppearsInParams() {
		$auth_params = WP_Auth0_LoginManager::get_authorize_params( '__test_connection__' );
		$this->assertEquals( '__test_connection__', $auth_params['connection'] );
	}

	public function testThatRedirectPassedAppearsInState() {
		$auth_params   = WP_Auth0_LoginManager::get_authorize_params( null, 'http://example.org/param-redirect' );
		$decoded_state = json_decode( base64_decode( $auth_params['state'] ) );
		$this->assertEquals( 'http://example.org/param-redirect', $decoded_state->redirect_to );
	}

	public function testThatParamFilterIsApplied() {
		add_filter( 'auth0_authorize_url_params', [ $this, 'filter_auth_parms' ], 10, 3 );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( 'auth0', 'https://auth0.com' );

		$this->assertEquals( 'https://auth0.com', $auth_params['auth0'] );
		$this->assertEquals( 'id_token code', $auth_params['response_type'] );

		remove_filter( 'auth0_authorize_url_params', [ $this, 'filter_auth_parms' ], 10 );
	}

	public function testThatStateFilterIsApplied() {
		add_filter( 'auth0_authorize_state', [ $this, 'filter_state_parms' ], 10, 2 );

		$auth_params   = WP_Auth0_LoginManager::get_authorize_params();
		$decoded_state = json_decode( base64_decode( $auth_params['state'] ) );

		$this->assertEquals( '__test_param_value__', $decoded_state->some_state_prop );
		$this->assertEquals( 'code', $decoded_state->response_type_repeat );

		remove_filter( 'auth0_authorize_state', [ $this, 'filter_state_parms' ], 10 );
	}

	public function testThatMaxAgeFilterIsApplied() {
		$auth_params = WP_Auth0_LoginManager::get_authorize_params();

		$this->assertArrayNotHasKey( 'max_age', $auth_params );

		add_filter( 'auth0_jwt_max_age', [ $this, 'filter_max_age' ], 10, 2 );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params();

		$this->assertArrayHasKey( 'max_age', $auth_params );
		$this->assertEquals( 1234, $auth_params['max_age'] );

		remove_filter( 'auth0_jwt_max_age', [ $this, 'filter_max_age' ], 10 );
	}

	/**
	 * Helper method to filter the auth params for testing.
	 *
	 * @param array       $params - Existing params.
	 * @param string|null $connection - Connection param value, if any.
	 * @param string      $redirect_to - Redirect value added to state.
	 *
	 * @return array
	 */
	public function filter_auth_parms( $params, $connection, $redirect_to ) {
		$params[ $connection ]   = $redirect_to;
		$params['response_type'] = 'id_token code';
		return $params;
	}

	/**
	 * Helper method to filter the state params for testing.
	 *
	 * @param array $state - Existing state properties.
	 * @param array $filtered_params - Existing auth params.
	 *
	 * @return array
	 */
	public function filter_state_parms( $state, $filtered_params ) {
		$state['some_state_prop']      = '__test_param_value__';
		$state['response_type_repeat'] = $filtered_params['response_type'];
		return $state;
	}

	/**
	 * Helper method to filter max_age for testing.
	 *
	 * @param null $max_age_null - Existing max_age setting.
	 *
	 * @return int
	 */
	public function filter_max_age( $max_age_null ) {
		return 1234;
	}
}
