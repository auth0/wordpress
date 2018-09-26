<?php
/**
 * Contains WP_Auth0_Api_Delete_User_Mfa.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Api_Delete_User_Mfa to perform a client credentials grant.
 */
class WP_Auth0_Api_Delete_User_Mfa extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 *
	 * @var integer
	 */
	const RETURN_ON_FAILURE = 0;

	/**
	 * Decoded token received.
	 *
	 * @var null|object
	 */
	protected $token_decoded = null;

	/**
	 * WP_Auth0_Api_Delete_User_Mfa constructor.
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
		$this->send_audience();
	}

	/**
	 * Set the user_id and provider, set the authorization header, call the API, and handle the response.
	 *
	 * @param string|null $user_id - Auth0 user ID to delete the MFA provider.
	 * @param string      $provider - MFA provider.
	 *
	 * @return int|mixed
	 */
	public function call( $user_id = null, $provider = 'google-authenticator' ) {

		if ( empty( $user_id ) || empty( $provider ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( 'update:users' ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( sprintf( 'api/v2/users/%s/multifactor/%s', $user_id, $provider ) )
			->delete()
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

		if ( $this->handle_failed_response( $method, 204 ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return 1;
	}
}
