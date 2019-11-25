<?php
/**
 * Contains Trait HttpHelpers.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Trait HttpHelpers.
 */
trait HttpHelpers {

	/**
	 * Mocked HTTP response to return.
	 *
	 * @var string|null
	 */
	protected $http_request_type = null;

	/**
	 * Start halting all HTTP requests.
	 * Use this at the top of tests that should check HTTP requests.
	 */
	public function startHttpHalting() {
		add_filter( 'pre_http_request', [ $this, 'httpHalt' ], 1, 3 );
	}

	/**
	 * Halt all HTTP requests with request data serialized in the error message.
	 *
	 * @param false|array $preempt - Original preempt value.
	 * @param array       $args - HTTP request arguments.
	 * @param string      $url - The request URL.
	 *
	 * @throws Exception - Always.
	 */
	public function httpHalt( $preempt, $args, $url ) {
		$error_msg = serialize(
			[
				'url'     => $url,
				'method'  => $args['method'],
				'headers' => $args['headers'],
				'body'    => is_string( $args['body'] ) ? json_decode( $args['body'], true ) : $args['body'],
				'preempt' => $preempt,
			]
		);
		throw new Exception( $error_msg );
	}

	/**
	 * Stop halting HTTP requests.
	 * Use this in a tearDown() method in the test suite.
	 */
	public function stopHttpHalting() {
		remove_filter( 'pre_http_request', [ $this, 'httpHalt' ], 1 );
	}

	/**
	 * Start mocking all HTTP requests.
	 * Use this at the top of tests that should test behavior for different HTTP responses.
	 */
	public function startHttpMocking() {
		add_filter( 'pre_http_request', [ $this, 'httpMock' ], 1, 3 );
	}

	/**
	 * Get the current http_request_type.
	 *
	 * @return string|null
	 */
	public function getResponseType() {
		if ( is_array( $this->http_request_type ) ) {
			return array_shift( $this->http_request_type );
		}
		return $this->http_request_type;
	}

	/**
	 * Mock returns from the HTTP client.
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
		switch ( $response_type ?: $this->getResponseType() ) {

			case 'halt':
				$this->httpHalt( false, $args, $url );
				return new WP_Error( 3, 'Halted.' );

			case 'wp_error':
				return new WP_Error( 1, 'Caught WP_Error.' );

			case 'auth0_api_error':
				return [
					'body'     => '{"statusCode":"caught_api_error","message":"Error","errorCode":"error_code"}',
					'response' => [ 'code' => 400 ],
				];

			case 'auth0_callback_error':
				return [
					'body'     => '{"error":"caught_callback_error","error_description":"Auth0 callback error"}',
					'response' => [ 'code' => 400 ],
				];

			case 'auth0_access_denied':
				return [
					'body'     => '{"error":"access_denied","error_description":"Unauthorized"}',
					'response' => [ 'code' => 401 ],
				];

			case 'other_error':
				return [
					'body'     => '{"other_error":"Other error"}',
					'response' => [ 'code' => 500 ],
				];

			case 'success_empty_body':
				return [
					'body'     => '',
					'response' => [ 'code' => 200 ],
				];

			case 'success_create_empty_body':
				return [
					'body'     => '',
					'response' => [ 'code' => 201 ],
				];

			case 'success_create_connection':
				return [
					'body'     => '{"id":"TEST_CREATED_CONN_ID"}',
					'response' => [ 'code' => 201 ],
				];

			case 'success_update_connection':
				return [
					'body'     => '{"id":"TEST_UPDATED_CONN_ID"}',
					'response' => [ 'code' => 200 ],
				];

			case 'success_get_connections':
				return [
					'body'     => '[{
						"id":"TEST_CONN_ID",
						"name":"TEST_CONNECTION",
						"enabled_clients":["TEST_CLIENT_ID"],
						"options":{"passwordPolicy":"poor"}
					}]',
					'response' => [ 'code' => 200 ],
				];

			case 'success_get_user':
				return [
					'body'     => '{
					    "email_verified": true,
					    "email": "user@example.org",
					    "user_id": "auth0|1234567890",
					    "user_metadata": {
					        "user_meta_key": "user_meta_value"
					    },
					    "app_metadata": {
					        "app_meta_key": "app_meta_value"
					    }
					}',
					'response' => [ 'code' => 200 ],
				];

			case 'success_access_token':
				return [
					'body'     => '{
						"access_token":"__test_access_token__",
						"scope":"update:users read:users",
						"expires_in":1000
					}',
					'response' => [ 'code' => 200 ],
				];

			case 'success_code_exchange':
				return [
					'body'     => '{
						"access_token":"__test_access_token__",
						"id_token":"__test_id_token__",
						"scope":"openid profile email",
						"expires_in":86400,
						"token_type":"Bearer"
					}',
					'response' => [ 'code' => 200 ],
				];

			case 'success_jwks':
				return [
					'body'     => '{"keys":[{"x5c":["__test_x5c_1__"],"kid":"__test_kid_1__"}]}',
					'response' => [ 'code' => 200 ],
				];

			default:
				return new WP_Error( 2, 'No mock type found.' );
		}
	}

	/**
	 * Stop mocking API calls.
	 * Use this in a tearDown() method in the test suite.
	 */
	public function stopHttpMocking() {
		remove_filter( 'pre_http_request', [ $this, 'httpMock' ], 1 );
	}
}
