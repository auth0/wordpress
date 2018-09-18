<?php
/**
 * Contains Class Test_WP_Auth0_Api_Abstract.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class Test_WP_Auth0_Api_Abstract.
 * Used to test WP_Auth0_Api_Abstract with a few additional helper functions.
 */
class Test_WP_Auth0_Api_Abstract extends WP_Auth0_Api_Abstract {

	/**
	 * HTTP method to use.
	 *
	 * @var string
	 */
	protected $http_method = 'get';

	/**
	 * Make a call to the HTTP method set in $this->set_http_method().
	 *
	 * @return mixed
	 *
	 * @throws Exception - When HTTP method is not set.
	 */
	public function call() {
		if ( empty( $this->http_method ) ) {
			throw new Exception( 'No HTTP method set. Call $this->set_http_method() first.' );
		}
		return $this->{$this->http_method}()->handle_response( __METHOD__ );
	}

	/**
	 * Stub method required to extend WP_Auth0_Api_Abstract.
	 *
	 * @param string $method - Calling method name, __METHOD__.
	 *
	 * @return boolean
	 */
	public function handle_response( $method ) {
		if ( $this->handle_wp_error( $method ) ) {
			return 'caught_wp_error';
		}

		if ( $this->handle_failed_response( $method ) ) {
			return 'caught_failed_response';
		}

		return 'completed_successfully';
	}

	/**
	 * Set the HTTP method used by $this->call.
	 * Always call this before $this->call.
	 *
	 * @param string $method - HTTP method to use.
	 *
	 * @return $this
	 *
	 * @throws Exception - If the method does not exist.
	 */
	public function set_http_method( $method ) {
		if ( ! method_exists( $this, $method ) ) {
			throw new Exception( 'Method ' . $method . ' does not exist.' );
		}
		$this->http_method = $method;
		return $this;
	}

	/**
	 * Return request in its current state.
	 *
	 * @param null $key - Request array key to return.
	 *
	 * @return array|mixed
	 */
	public function get_request( $key = null ) {
		$request = array(
			'body'    => $this->body,
			'headers' => $this->headers,
			'url'     => $this->build_url(),
		);
		return $key && array_key_exists( $key, $request ) ? $request[ $key ] : $request;
	}
}
