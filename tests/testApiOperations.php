<?php
/**
 * Contains Class TestApiOperations.
 *
 * @package WP-Auth0
 *
 * @since 3.8.1
 */

/**
 * Class TestApiOperations.
 */
class TestApiOperations extends WP_Auth0_Test_Case {

	use HttpHelpers;

	/**
	 * Test that a basic create connection command requests properly.
	 */
	public function testThatCreateConnectionRequestsCorrectly() {
		$this->startHttpHalting();

		$api_ops    = new WP_Auth0_Api_Operations( self::$opts );
		$test_token = implode( '.', [ uniqid(), uniqid(), uniqid() ] );

		self::$opts->set( 'domain', 'test-wp.auth0.com' );
		self::$opts->set( 'client_id', 'TEST_CLIENT_ID' );

		$caught_http = [];
		try {
			$api_ops->create_wordpress_connection( $test_token, false, 'good' );
		} catch ( Exception $e ) {
			$caught_http = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test-wp.auth0.com/api/v2/connections', $caught_http['url'] );
		$this->assertEquals( 'Bearer ' . $test_token, $caught_http['headers']['Authorization'] );
		$this->assertEquals( 'DB-Test-Blog', $caught_http['body']['name'] );
		$this->assertEquals( 'auth0', $caught_http['body']['strategy'] );
		$this->assertEquals( 'good', $caught_http['body']['options']['passwordPolicy'] );
		$this->assertContains( 'TEST_CLIENT_ID', $caught_http['body']['enabled_clients'] );
	}

	/**
	 * Test that a migration create connection command requests properly.
	 */
	public function testThatCreateConnectionWithMigrationRequestsCorrectly() {
		$this->startHttpHalting();

		$api_ops = new WP_Auth0_Api_Operations( self::$opts );

		self::$opts->set( 'domain', 'test-wp2.auth0.com' );
		self::$opts->set( 'client_id', '__test_client_id__' );

		$caught_http = [];
		try {
			$api_ops->create_wordpress_connection( '__test_api_token__', true, 'fair', 'TEST_MIGRATION_TOKEN' );
		} catch ( Exception $e ) {
			$caught_http = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test-wp2.auth0.com/api/v2/connections', $caught_http['url'] );
		$this->assertEquals( 'Bearer ' . '__test_api_token__', $caught_http['headers']['Authorization'] );
		$this->assertEquals( 'DB-Test-Blog', $caught_http['body']['name'] );
		$this->assertEquals( 'auth0', $caught_http['body']['strategy'] );
		$this->assertEquals( [ '__test_client_id__' ], $caught_http['body']['enabled_clients'] );

		$this->assertEquals( 'fair', $caught_http['body']['options']['passwordPolicy'] );
		$this->assertEquals( true, $caught_http['body']['options']['import_mode'] );
		$this->assertEquals( true, $caught_http['body']['options']['requires_username'] );
		$this->assertEquals( true, $caught_http['body']['options']['requires_username'] );
		$this->assertEquals( true, $caught_http['body']['options']['import_mode'] );
		$this->assertEquals( true, $caught_http['body']['options']['enabledDatabaseCustomization'] );
		$this->assertEquals(
			[
				'min' => 1,
				'max' => 100,
			],
			$caught_http['body']['options']['validation']['username']
		);

		$this->assertArrayHasKey( 'customScripts', $caught_http['body']['options'] );
		$this->assertArrayHasKey( 'login', $caught_http['body']['options']['customScripts'] );
		$this->assertArrayHasKey( 'get_user', $caught_http['body']['options']['customScripts'] );

		$login_script = explode( PHP_EOL, $caught_http['body']['options']['customScripts']['login'] );
		$login_script = array_map( 'trim', $login_script );
		$this->assertContains( "configuration.endpointUrl + 'migration-ws-login',", $login_script );
		$this->assertContains( 'access_token: configuration.migrationToken', $login_script );
		$this->assertContains( "user_id: configuration.userNamespace + '|' + wpUser.data.ID,", $login_script );

		$get_user_script = explode( PHP_EOL, $caught_http['body']['options']['customScripts']['get_user'] );
		$get_user_script = array_map( 'trim', $get_user_script );
		$this->assertContains( "configuration.endpointUrl + 'migration-ws-get-user',", $get_user_script );
		$this->assertContains( 'access_token: configuration.migrationToken', $get_user_script );
		$this->assertContains( "user_id: configuration.userNamespace + '|' + wpUser.data.ID,", $get_user_script );

		$this->assertArrayHasKey( 'bareConfiguration', $caught_http['body']['options'] );
		$this->assertArrayHasKey( 'endpointUrl', $caught_http['body']['options']['bareConfiguration'] );
		$this->assertArrayHasKey( 'migrationToken', $caught_http['body']['options']['bareConfiguration'] );
		$this->assertArrayHasKey( 'userNamespace', $caught_http['body']['options']['bareConfiguration'] );

		$this->assertEquals(
			'http://example.org/index.php?a0_action=',
			$caught_http['body']['options']['bareConfiguration']['endpointUrl']
		);

		$this->assertEquals(
			'TEST_MIGRATION_TOKEN',
			$caught_http['body']['options']['bareConfiguration']['migrationToken']
		);

		$this->assertEquals(
			get_auth0_curatedBlogName(),
			$caught_http['body']['options']['bareConfiguration']['userNamespace']
		);
	}

	/**
	 * Test that successful and unsuccessful requests return properly.
	 */
	public function testThatCreateConnectionReturnsCorrectly() {
		$this->startHttpMocking();

		$api_ops    = new WP_Auth0_Api_Operations( self::$opts );
		$test_token = implode( '.', [ uniqid(), uniqid(), uniqid() ] );

		self::$opts->set( 'domain', 'test-wp.auth0.com' );
		self::$opts->set( 'client_id', 'TEST_CLIENT_ID' );

		$this->http_request_type = 'success_create_connection';

		$result = $api_ops->create_wordpress_connection( $test_token, false );

		$this->assertEquals( 'TEST_CREATED_CONN_ID', $result );
		$this->assertEquals( 'DB-Test-Blog', self::$opts->get( 'db_connection_name' ) );

		$this->http_request_type = 'wp_error';

		$result = $api_ops->create_wordpress_connection( $test_token, false );

		$this->assertFalse( $result );
	}
}
