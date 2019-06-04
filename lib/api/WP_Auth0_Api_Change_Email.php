<?php
/**
 * Contains WP_Auth0_Api_Change_Email.
 *
 * @package WP-Auth0
 *
 * @since 3.9.0
 */

/**
 * Class WP_Auth0_Api_Change_Email to update a user's email at Auth0.
 */
class WP_Auth0_Api_Change_Email extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = false;

	/**
	 * Required scope for Management API token.
	 */
	const API_SCOPE = 'update:users';

	/**
	 * Decoded token received for the Management API.
	 *
	 * @var null|object
	 */
	protected $token_decoded = null;

	/**
	 * WP_Auth0_Api_Change_Email constructor.
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
	 * Set the user_id and email, make the API call, and handle the response.
	 *
	 * @param string|null $user_id - Auth0 user ID to change the email for.
	 * @param string|null $email - New email.
	 *
	 * @return bool|string
	 */
	public function call( $user_id = null, $email = null ) {

		if ( empty( $user_id ) || empty( $email ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( self::API_SCOPE ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'api/v2/users/' . rawurlencode( $user_id ) )
			->add_body( 'email', $email )
			// Email is either changed by an admin or verified by WP.
			->add_body( 'email_verified', true )
			->add_body( 'client_id', $this->options->get( 'client_id' ) )
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
			return self::RETURN_ON_FAILURE;
		}

		return true;
	}
}
