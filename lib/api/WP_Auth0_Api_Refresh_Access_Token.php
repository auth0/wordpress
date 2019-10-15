<?php
/**
 * Contains WP_Auth0_Api_Refresh_Access_Token.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class WP_Auth0_Api_Refresh_Access_Token
 * Get a new access token using the refresh token of a user.
 *
 * @see https://auth0.com/docs/tokens/refresh-token/current
 * @see https://auth0.com/docs/api/authentication#refresh-token
 */
class WP_Auth0_Api_Refresh_Access_Token extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Make the API call and handle the response.
	 *
	 * @param string|null $client_id - Client ID to use.
	 * @param string|null $client_secret - Client Secret to use.
	 * @param string|null $refresh_token - Client's refresh token to use.
	 *
	 * @return null|string
	 */
	public function call( $client_id = null, $client_secret = null, $refresh_token = null ) {
		
		if ( empty( $refresh_token ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$client_id = $client_id ?: $this->options->get( 'client_id' );
		if ( empty( $client_id ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$client_secret = $client_secret ?: $this->options->get( 'client_secret' );
		if ( empty( $client_secret ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'oauth/token' )
			->add_body( 'grant_type', 'refresh_token' )
			->add_body( 'client_id', $client_id )
			->add_body( 'client_secret', $client_secret )
			->add_body( 'refresh_token', $refresh_token )
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
			WP_Auth0_ErrorManager::insert_auth0_error(
				__METHOD__ . ' L:' . __LINE__,
				__( 'An /oauth/token call triggered a 401 response from Auth0. ', 'wp-auth0' ) .
				__( 'Please check the Client ID and Client Secret saved in the Auth0 plugin settings. ', 'wp-auth0' )
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
