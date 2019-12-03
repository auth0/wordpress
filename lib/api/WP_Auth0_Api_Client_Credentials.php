<?php
/**
 * Contains WP_Auth0_Api_Client_Credentials.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Api_Client_Credentials to perform a client credentials grant.
 */
class WP_Auth0_Api_Client_Credentials extends WP_Auth0_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Transient key for API token.
	 */
	const TOKEN_TRANSIENT_KEY = 'auth0_api_token';

	/**
	 * Transient key for API token scope.
	 */
	const SCOPE_TRANSIENT_KEY = 'auth0_api_token_scope';

	/**
	 * WP_Auth0_Api_Client_Credentials constructor.
	 *
	 * @param WP_Auth0_Options $options - WP_Auth0_Options instance.
	 */
	public function __construct( WP_Auth0_Options $options ) {
		parent::__construct( $options );
		$this->set_path( 'oauth/token' )
			->send_client_id()
			->send_client_secret()
			->send_audience()
			->add_body( 'grant_type', 'client_credentials' );
	}

	/**
	 * Make the API call and handle the response.
	 *
	 * @return mixed|null
	 */
	public function call() {
		return $this->post()->handle_response( __METHOD__ );
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

		$response_body = json_decode( $this->response_body );

		// If we have no access token, something went wrong upstream.
		if ( empty( $response_body->access_token ) ) {
			WP_Auth0_ErrorManager::insert_auth0_error( $method, __( 'No access_token returned.', 'wp-auth0' ) );
			return self::RETURN_ON_FAILURE;
		}

		// Set the transient to expire 1 minute before the token does.
		$expires_in  = ! empty( $response_body->expires_in ) ? absint( $response_body->expires_in ) : HOUR_IN_SECONDS;
		$expires_in -= MINUTE_IN_SECONDS;

		// Store the token and scope to check when used.
		set_transient( self::TOKEN_TRANSIENT_KEY, $response_body->access_token, $expires_in );
		set_transient( self::SCOPE_TRANSIENT_KEY, $response_body->scope, $expires_in );

		return $response_body->access_token;
	}

	/**
	 * Get the stored access token from a transient.
	 *
	 * @return string
	 */
	public static function get_stored_token() {
		return get_transient( self::TOKEN_TRANSIENT_KEY );
	}

	/**
	 * Delete the stored access token and scope from transients.
	 */
	public static function delete_store() {
		delete_transient( self::TOKEN_TRANSIENT_KEY );
		delete_transient( self::SCOPE_TRANSIENT_KEY );
	}

	/**
	 * Check a single scope against the scope of the stored access token.
	 *
	 * @param string $scope - Single scope to check for.
	 *
	 * @return bool
	 */
	public static function check_stored_scope( $scope ) {
		$stored_scope = get_transient( self::SCOPE_TRANSIENT_KEY );
		$scopes       = explode( ' ', $stored_scope );
		return in_array( $scope, $scopes );
	}
}
