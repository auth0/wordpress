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
	 *
	 * @var null
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Decoded token received.
	 *
	 * @var null|object
	 */
	protected $token_decoded = null;

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
	 * Return the decoded API token received.
	 *
	 * @return null|object
	 */
	public function get_token_decoded() {
		return $this->token_decoded;
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

		if ( empty( $response_body->access_token ) ) {
			WP_Auth0_ErrorManager::insert_auth0_error( $method, __( 'No access_token returned.', 'wp-auth0' ) );
			return self::RETURN_ON_FAILURE;
		}

		try {
			$this->token_decoded = $this->decode_jwt( $response_body->access_token );
			return $response_body->access_token;
		} catch ( Exception $e ) {
			WP_Auth0_ErrorManager::insert_auth0_error( $method, $e );
			return self::RETURN_ON_FAILURE;
		}
	}
}