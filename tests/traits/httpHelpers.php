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
				'body'    => json_decode( $args['body'], true ),
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
		add_filter( 'pre_http_request', [ $this, 'httpMock' ], 1 );
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
	 * @param null|string $response_type - Response type to use.
	 *
	 * @return array|WP_Error
	 */
	public function httpMock( $response_type = null ) {
		switch ( $response_type ?: $this->getResponseType() ) {

			case 'wp_error':
				return new WP_Error( 1, 'Caught WP_Error.' );

			case 'auth0_api_error':
				return [
					'body'     => '{"statusCode":"caught_api_error","message":"Error","errorCode":"error_code"}',
					'response' => [ 'code' => 400 ],
				];

			case 'auth0_callback_error':
				return [
					'body'     => '{"error":"caught_callback_error","error_description":"Error"}',
					'response' => [ 'code' => 400 ],
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
