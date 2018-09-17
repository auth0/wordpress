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
	 * Set the User ID to and provider to delete.
	 *
	 * @param string $user_id - Auth0 user ID.
	 * @param string $provider - Provider name.
	 *
	 * @return WP_Auth0_Api_Delete_User_Mfa
	 */
	public function init_path( $user_id, $provider = 'google-authenticator' ) {
		$this->set_path( sprintf( 'api/v2/users/%s/multifactor/%s', $user_id, $provider ) );
		return $this;
	}

	/**
	 * Set body data, make the API call, and handle the response.
	 *
	 * @return mixed|null
	 */
	public function call() {

		if ( ! $this->set_bearer( 'update:users' ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->send_audience( 'api/v2/' )
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
