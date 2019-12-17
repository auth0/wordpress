<?php
/**
 * Contains WP_Auth0_Api_Exchange_Code.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class WP_Auth0_Api_Exchange_Code
 * Exchange an authorization code for tokens.
 *
 * @see https://auth0.com/docs/api/authentication#authorization-code-flow
 */
class WP_Auth0_Api_Exchange_Code extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Make the API call and handle the response.
	 *
	 * @param string|null $code - Authorization code to exchange for tokens.
	 * @param string|null $client_id - Client ID to use.
	 * @param string|null $redirect_uri - Redirect URI to use.
	 *
	 * @return null|string
	 */
	public function call( $code = null, $client_id = null, $redirect_uri = null ) {

		if ( empty( $code ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$client_id = $client_id ?: $this->options->get( 'client_id' );
		if ( empty( $client_id ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$client_secret = $this->options->get( 'client_secret' ) ?: '';
		$redirect_uri  = $redirect_uri ?: $this->options->get_wp_auth0_url();

		return $this
			->set_path( 'oauth/token' )
			->add_body( 'grant_type', 'authorization_code' )
			->add_body( 'code', $code )
			->add_body( 'redirect_uri', $redirect_uri )
			->add_body( 'client_id', $client_id )
			->add_body( 'client_secret', $client_secret )
			->post()
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

		if ( 401 == $this->response_code ) {
			WP_Auth0_ErrorLog::insert_error(
				__METHOD__ . ' L:' . __LINE__,
				__( 'An /oauth/token call triggered a 401 response from Auth0. ', 'wp-auth0' ) .
				__( 'Please check the Client Secret saved in the Auth0 plugin settings. ', 'wp-auth0' )
			);
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this->response_body;
	}
}
