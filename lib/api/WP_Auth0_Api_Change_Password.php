<?php
/**
 * Contains WP_Auth0_Api_Change_Password.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Api_Change_Password to perform a client credentials grant.
 */
class WP_Auth0_Api_Change_Password extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 *
	 * @var boolean
	 */
	const RETURN_ON_FAILURE = false;

	/**
	 * Decoded token received.
	 *
	 * @var null|object
	 */
	protected $token_decoded = null;

	/**
	 * Set the User ID to change.
	 *
	 * @param string $user_id - Auth0 user ID.
	 *
	 * @return WP_Auth0_Api_Change_Password
	 */
	public function init_path( $user_id ) {
		$this->set_path( 'api/v2/users/' . rawurlencode( $user_id ) );
		return $this;
	}

	/**
	 * Set body data, make the API call, and handle the response.
	 *
	 * @param array $body - Body array to send.
	 *
	 * @return int|mixed
	 */
	public function call( array $body = array() ) {

		if ( ! $this->set_bearer( 'update:users' ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( empty( $body ) || empty( $body['password'] ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->add_body( 'password', $body['password'] )
			->patch()
			->handle_response( __METHOD__ );
	}

	/**
	 * Handle API response.
	 *
	 * @param string $method - Method that called the API.
	 *
	 * @return integer
	 */
	protected function handle_response( $method ) {

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method ) ) {
			$response_body = json_decode( $this->response_body );
			if ( isset( $response_body->message ) && false !== strpos( $response_body->message, 'PasswordStrengthError' ) ) {
				return __( 'Password is too weak, please choose a different one.', 'wp-auth0' );
			}
			return self::RETURN_ON_FAILURE;
		}

		return true;
	}
}
