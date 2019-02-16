<?php
/**
 * Contains Class TestOptionPasswordPolicy.
 *
 * @package WP-Auth0
 *
 * @since 3.8.1
 */

/**
 * Class TestOptionPasswordPolicy.
 */
class TestOptionPasswordPolicy extends WP_Auth0_Test_Case {

	use HttpHelpers;

	use RedirectHelpers;

	/**
	 * Test that the validation does not run if the current value matches the old value.
	 */
	public function testThatInputIsNotValidatedIfItIsNotChanged() {
		$admin_features = new WP_Auth0_Admin_Features( self::$opts );

		$old_input = [ 'password_policy' => 'fair' ];
		$new_input = [ 'password_policy' => 'fair' ];
		$result    = $admin_features->security_validation( $old_input, $new_input );

		$this->assertEquals( $old_input, $result );

		global $wp_settings_errors;
		$this->assertEmpty( $wp_settings_errors );
	}

	/**
	 * Test the connection search request structure.
	 */
	public function testThatConnectionsAreSearchedDuringValidation() {
		$this->startHttpHalting();

		$test_domain    = 'test-wp.auth0.com';
		$test_token     = implode( '.', [ uniqid(), uniqid(), uniqid() ] );
		$admin_features = new WP_Auth0_Admin_Features( self::$opts );

		$old_input = [ 'password_policy' => 'fair' ];
		$new_input = [
			'password_policy' => 'good',
			'domain'          => $test_domain,
			'auth0_app_token' => $test_token, // TO BE DEPRECATED.
		];

		$caught_request = [];
		try {
			$admin_features->security_validation( $old_input, $new_input );
		} catch ( Exception $e ) {
			$caught_request = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test-wp.auth0.com/api/v2/connections?strategy=auth0', $caught_request['url'] );
		$this->assertEquals( 'Bearer ' . $test_token, $caught_request['headers']['Authorization'] );
		$this->assertEmpty( $caught_request['body'] );
	}

	/**
	 * Test that we get a UI error if there are no connections to set.
	 */
	public function testThatAnErrorIsSetIfThereAreNoConnections() {
		$this->startHttpMocking();

		$admin_features = new WP_Auth0_Admin_Features( self::$opts );

		$old_input = [ 'password_policy' => 'fair' ];
		$new_input = [
			'password_policy' => 'good',
			'domain'          => 'test-wp.auth0.com',
			'auth0_app_token' => implode( '.', [ uniqid(), uniqid(), uniqid() ] ), // TO BE DEPRECATED.
		];

		$this->http_request_type = 'success_empty_body';

		$result = $admin_features->security_validation( $old_input, $new_input );

		global $wp_settings_errors;

		$this->assertContains( 'No database connections found', $wp_settings_errors[0]['message'] );
		$this->assertContains( 'https://manage.auth0.com/#/connections/database', $wp_settings_errors[0]['message'] );
		$this->assertEquals( 'wp_auth0_settings', $wp_settings_errors[0]['code'] );
		$this->assertEquals( 'wp_auth0_settings', $wp_settings_errors[0]['setting'] );
		$this->assertEquals( $old_input['password_policy'], $result['password_policy'] );
	}

	/**
	 * Test that the update connection request is set properly.
	 */
	public function testThatAnUpdateRequestIsSent() {
		$this->startHttpMocking();

		$admin_features = new WP_Auth0_Admin_Features( self::$opts );

		$old_input = [ 'password_policy' => 'fair' ];
		$new_input = [
			'password_policy' => 'good',
			'domain'          => 'test-wp.auth0.com',
			'auth0_app_token' => implode( '.', [ uniqid(), uniqid(), uniqid() ] ), // TO BE DEPRECATED.
			'client_id'       => 'TEST_CLIENT_ID',
		];

		$this->http_request_type = [ 'success_get_connections', 'halt' ];

		$caught_request = [ 'Nothing caught' ];
		try {
			$admin_features->security_validation( $old_input, $new_input );
		} catch ( Exception $e ) {
			$caught_request = unserialize( $e->getMessage() );
		}

		$this->assertEquals( 'https://test-wp.auth0.com/api/v2/connections/TEST_CONN_ID', $caught_request['url'] );
		$this->assertEquals( 'Bearer ' . $new_input['auth0_app_token'], $caught_request['headers']['Authorization'] ); // TO BE DEPRECATED.
		$this->assertContains( $new_input['client_id'], $caught_request['body']['enabled_clients'] );
		$this->assertEquals( $new_input['password_policy'], $caught_request['body']['options']['passwordPolicy'] );
	}

	/**
	 * Test that we get a UI error if the connection cannot be updated properly.
	 */
	public function testThatAnErrorIsSetIfConnectionUpdateFails() {
		$this->startHttpMocking();

		$admin_features = new WP_Auth0_Admin_Features( self::$opts );

		$old_input = [ 'password_policy' => 'fair' ];
		$new_input = [
			'password_policy' => 'good',
			'domain'          => 'test-wp.auth0.com',
			'auth0_app_token' => implode( '.', [ uniqid(), uniqid(), uniqid() ] ), // TO BE DEPRECATED.
			'client_id'       => 'TEST_CLIENT_ID',
		];

		$this->http_request_type = [ 'success_get_connections', 'wp_error' ];

		$result = $admin_features->security_validation( $old_input, $new_input );

		global $wp_settings_errors;

		$this->assertContains( 'There was a problem updating the password policy.', $wp_settings_errors[0]['message'] );
		$this->assertContains( 'https://manage.auth0.com/#/connections/database', $wp_settings_errors[0]['message'] );
		$this->assertEquals( 'wp_auth0_settings', $wp_settings_errors[0]['code'] );
		$this->assertEquals( 'wp_auth0_settings', $wp_settings_errors[0]['setting'] );
		$this->assertEquals( $old_input['password_policy'], $result['password_policy'] );
	}

	/**
	 * Test that an end-to-end update is successful.
	 */
	public function testThatPasswordPolicyUpdatesWithoutErrors() {
		$this->startHttpMocking();

		$admin_features = new WP_Auth0_Admin_Features( self::$opts );

		$old_input = [ 'password_policy' => 'fair' ];
		$new_input = [
			'password_policy' => 'good',
			'domain'          => 'test-wp.auth0.com',
			'auth0_app_token' => implode( '.', [ uniqid(), uniqid(), uniqid() ] ), // TO BE DEPRECATED.
			'client_id'       => 'TEST_CLIENT_ID',
		];

		$this->http_request_type = [ 'success_get_connections', 'success_update_connection' ];

		$result = $admin_features->security_validation( $old_input, $new_input );
		$this->assertEquals( $new_input['password_policy'], $result['password_policy'] );

		global $wp_settings_errors;
		$this->assertEmpty( $wp_settings_errors );
	}
}
