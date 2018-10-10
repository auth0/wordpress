<?php
/**
 * Contains WP_Auth0_Api_Jobs_Verification.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Api_Jobs_Verification to resend a verification email.
 */
class WP_Auth0_Api_Jobs_Verification extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = false;

	/**
	 * Required scope for API token.
	 */
	const API_SCOPE = 'update:users';

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	protected $api_client_creds;

	/**
	 * WP_Auth0_Api_Jobs_Verification constructor.
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
		$this->set_path( 'api/v2/jobs/verification-email' )
			->send_client_id();
	}

	/**
	 * Set body data, make the API call, and handle the response.
	 *
	 * @param string|null $user_id - Auth0 user ID to send the verify email to.
	 *
	 * @return bool|mixed|null
	 */
	public function call( $user_id = null ) {

		if ( empty( $user_id ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( self::API_SCOPE ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this->add_body( 'user_id', $user_id )
			->post()
			->handle_response( __METHOD__ );
	}

	/**
	 * Handle API response.
	 *
	 * @param string $method - Method that called the API.
	 *
	 * @return mixed|null
	 */
	protected function handle_response( $method ) {

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method, 201 ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return true;
	}
}
