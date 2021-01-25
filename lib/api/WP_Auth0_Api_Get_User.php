<?php
/**
 * Contains WP_Auth0_Api_Get_User.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class WP_Auth0_Api_Get_User
 * Get user information for an Auth0 user ID.
 */
class WP_Auth0_Api_Get_User extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Required scope for Management API token.
	 */
	const API_SCOPE = 'read:users';

	/**
	 * WP_Auth0_Api_Get_User constructor.
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
	 * Check the user_id, make the API call, and handle the response.
	 *
	 * @param string|null $user_id - Auth0 user ID to get.
	 *
	 * @return null|string
	 */
	public function call( $user_id = null ) {

		if ( empty( $user_id ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( self::API_SCOPE ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'api/v2/users/' . rawurlencode( $user_id ) )
			->get()
			->handle_response( __METHOD__ );
	}

	/**
	 * Handle API response.
	 *
	 * @param string $method - Method that called the API.
	 *
	 * @return string|null
	 */
	protected function handle_response( $method ) {

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this->response_body;
	}
}
