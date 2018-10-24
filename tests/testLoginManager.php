<?php
/**
 * Contains Class TestLoginManager.
 *
 * @package WP-Auth0
 * @since 3.7.1
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestLoginManager.
 * Tests that WP_Auth0_LoginManager methods function as expected.
 */
class TestLoginManager extends TestCase {

	use setUpTestDb;

	/**
	 * Test that the default auth scopes are returned and filtered properly.
	 */
	public function testUserinfoScope() {
		$scope = WP_Auth0_LoginManager::get_userinfo_scope();
		$this->assertEquals( 'openid email profile', $scope );

		add_filter(
			'auth0_auth_scope',
			function( $default_scope, $context ) {
				$default_scope[] = $context;
				return $default_scope;
			},
			10,
			2
		);

		$scope = WP_Auth0_LoginManager::get_userinfo_scope( 'auth0' );
		$this->assertEquals( 'openid email profile auth0', $scope );
	}

	/**
	 * Test that authorize URL params are built and filtered properly.
	 */
	public function testAuthorizeParams() {
		$test_client_id  = uniqid();
		$test_connection = uniqid();
		$auth_params     = WP_Auth0_LoginManager::get_authorize_params();

		$this->assertEquals( 'openid email profile', $auth_params['scope'] );
		$this->assertEquals( 'code', $auth_params['response_type'] );
		$this->assertEquals( site_url( 'index.php?auth0=1' ), $auth_params['redirect_uri'] );
		$this->assertNotEmpty( $auth_params['auth0Client'] );
		$this->assertNotEmpty( $auth_params['state'] );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( $test_connection );
		$this->assertEquals( $test_connection, $auth_params['connection'] );

		$options = WP_Auth0_Options::Instance();
		$options->set( 'client_id', $test_client_id );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params();
		$this->assertEquals( $test_client_id, $auth_params['client_id'] );

		$options->set( 'auth0_implicit_workflow', 1 );
		$auth_params = WP_Auth0_LoginManager::get_authorize_params();
		$this->assertEquals( add_query_arg( 'auth0', 'implicit', site_url( 'index.php' ) ), $auth_params['redirect_uri'] );
		$this->assertEquals( 'id_token', $auth_params['response_type'] );
		$this->assertNotEmpty( $auth_params['nonce'] );
		$this->assertEquals( 'form_post', $auth_params['response_mode'] );

		add_filter(
			'auth0_authorize_url_params',
			function( $params, $connection, $redirect_to ) {
				$params[ $connection ] = $redirect_to;
				return $params;
			},
			10,
			3
		);

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( 'auth0', 'https://auth0.com' );
		$this->assertEquals( 'https://auth0.com', $auth_params['auth0'] );
	}

	/**
	 * Test that the authorize URL is built properly.
	 */
	public function testBuildAuthorizeUrl() {
		$options = WP_Auth0_Options::Instance();

		// Basic authorize URL.
		$options->set( 'domain', 'test.auth0.com' );
		$options->set( 'custom_domain', '' );
		$auth_url = WP_Auth0_LoginManager::build_authorize_url();

		$this->assertEquals( 'https://test.auth0.com/authorize', $auth_url );

		// Custom domain authorize URL.
		$options->set( 'custom_domain', 'test-custom.auth0.com' );
		$auth_url = WP_Auth0_LoginManager::build_authorize_url();

		$this->assertEquals( 'https://test-custom.auth0.com/authorize', $auth_url );

		// Authorize URL with parameters.
		$auth_url = WP_Auth0_LoginManager::build_authorize_url(
			[
				'connection' => 'auth0',
				'prompt'     => 'none',
			]
		);

		$this->assertEquals( 'https://test-custom.auth0.com/authorize?connection=auth0&prompt=none', $auth_url );

		// Authorize URL with parameters that are URL encoded.
		$auth_url = WP_Auth0_LoginManager::build_authorize_url( [ 'connection' => 'this/that' ] );

		$this->assertEquals( 'https://test-custom.auth0.com/authorize?connection=this%2Fthat', $auth_url );

		// Authorize URL filter.
		add_filter(
			'auth0_authorize_url',
			function ( $auth_url, $params ) {
				return explode( '?', $auth_url )[0] . '?test=' . $params['test'];
			},
			10,
			2
		);

		$auth_url = WP_Auth0_LoginManager::build_authorize_url(
			[
				'test'       => 'this',
				'connection' => 'auth0',
			]
		);
		$this->assertEquals( 'https://test-custom.auth0.com/authorize?test=this', $auth_url );
	}
}
