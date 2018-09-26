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
	 * WP_Auth0_Api_Change_Password constructor.
	 *
	 * @param WP_Auth0_Options                $options - WP_Auth0_Options instance.
	 * @param WP_Auth0_Api_Client_Credentials $api_client_creds - WP_Auth0_Api_Client_Credentials instance.
	 */
	public function __construct(
		WP_Auth0_Options $options,
		WP_Auth0_Api_Client_Credentials $api_client_creds
	) {
		parent::__construct( $options );
		$this->api_client_creds = $api_client_creds;
	}

	/**
	 * Set the user_id and password, make the API call, and handle the response.
	 *
	 * @param null $user_id
	 * @param null $password
	 * @return bool|int|mixed
	 */
	public function call( $user_id = null, $password = null ) {

		if ( empty( $user_id ) || empty( $password ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( 'update:users' ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'api/v2/users/' . rawurlencode( $user_id ) )
			->add_body( 'password', $password )
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
