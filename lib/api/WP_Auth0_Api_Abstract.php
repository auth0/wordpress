<?php
/**
 * Contains WP_Auth0_Api_Abstract.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Api_Abstract for API calls.
 */
abstract class WP_Auth0_Api_Abstract {

	/**
	 * WP cache key.
	 * Used in combination with WPA0_CACHE_GROUP for namespacing.
	 */
	const CACHE_KEY = 'api_token';

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $options;

	/**
	 * Tenant Domain from plugin settings.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Client ID from plugin settings.
	 *
	 * @var string
	 */
	protected $client_id;

	/**
	 * Client Secret from plugin settings.
	 *
	 * @var string
	 */
	protected $client_secret;

	/**
	 * WP_Auth0_Api_Client_Credentials instance.
	 *
	 * @var WP_Auth0_Api_Client_Credentials
	 */
	protected $api_client_creds;

	/**
	 * API token from plugin settings or Client Credentials call.
	 *
	 * @deprecated - 3.10.0, not used.
	 *
	 * @var string
	 */
	protected $api_token;

	/**
	 * Decoded API token from plugin settings.
	 *
	 * @deprecated - 3.10.0, not used.
	 *
	 * @var object
	 */
	protected $api_token_decoded;

	/**
	 * API path.
	 *
	 * @var string
	 */
	protected $remote_path = '';

	/**
	 * Headers to send with the request.
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Body to send with the request.
	 *
	 * @var array
	 */
	protected $body = array();

	/**
	 * API response.
	 *
	 * @var mixed
	 */
	protected $response;

	/**
	 * API response code.
	 *
	 * @var integer
	 */
	protected $response_code = null;

	/**
	 * API response body.
	 *
	 * @var string
	 */
	protected $response_body = null;

	/**
	 * WP_Auth0_Api_Abstract constructor.
	 *
	 * @param WP_Auth0_Options $options - WP_Auth0_Options instance.
	 */
	public function __construct( WP_Auth0_Options $options ) {
		$this->options = $options;

		// Required settings in the plugin.
		$this->domain        = $this->options->get( 'domain' );
		$this->client_id     = $this->options->get( 'client_id' );
		$this->client_secret = $this->options->get( 'client_secret' );

		// Headers sent with every request.
		$this->headers = static::get_info_headers();
	}

	/**
	 * Get required telemetry header
	 *
	 * @return array
	 */
	public static function get_info_headers() {
		$header_value = array(
			'name'    => 'wp-auth0',
			'version' => WPA0_VERSION,
			'env'     => array(
				'php' => phpversion(),
				'wp'  => get_bloginfo( 'version' ),
			),
		);
		return array( 'Auth0-Client' => base64_encode( wp_json_encode( $header_value ) ) );
	}

	/**
	 * Call the API.
	 *
	 * @return mixed
	 */
	abstract function call();

	/**
	 * Handle the response.
	 *
	 * @param string $method - Calling method name.
	 *
	 * @return mixed
	 */
	abstract protected function handle_response( $method );

	/**
	 * Set the remote path to call.
	 *
	 * @param string $path - Path to use.
	 *
	 * @return $this
	 */
	protected function set_path( $path ) {
		$this->remote_path = $this->clean_path( $path );
		return $this;
	}

	/**
	 * Set the stored API Token or perform a Client Credentials grant to get a new access token.
	 *
	 * @param string $scope - Scope to check.
	 *
	 * @return bool
	 */
	protected function set_bearer( $scope ) {

		if ( ! $this->api_client_creds instanceof WP_Auth0_Api_Client_Credentials ) {
			return false;
		}

		$cc_api    = $this->api_client_creds;
		$api_token = $cc_api::get_stored_token();

		// No stored API token so need to get a new one.
		if ( ! $api_token ) {
			$api_token = $this->api_client_creds->call();
		}

		// No token to use, error recorded in previous steps.
		if ( ! $api_token ) {
			return false;
		}

		if ( $cc_api::check_stored_scope( $scope ) ) {
			// Scope exists, add to the header and cache.
			$this->add_header( 'Authorization', 'Bearer ' . $api_token );
			return true;
		}

		// API token is missing the required scope.
		WP_Auth0_ErrorManager::insert_auth0_error(
			__METHOD__,
			new WP_Error(
				'insufficient_scope',
				// translators: The $scope var here is a machine term and should not be translated.
				sprintf( __( 'API token does not include the scope %s.', 'wp-auth0' ), $scope )
			)
		);

		// Delete the stored token so we can try again.
		$cc_api::delete_store();
		return false;
	}

	/**
	 * Include the Management API audience in the body array.
	 *
	 * @return $this
	 */
	protected function send_audience() {
		$this->body['audience'] = 'https://' . $this->domain . '/api/v2/';
		return $this;
	}

	/**
	 * Include the Client ID in the body array.
	 *
	 * @return $this
	 */
	protected function send_client_id() {
		$this->body['client_id'] = $this->client_id;
		return $this;
	}

	/**
	 * Include the Client Secret in the body array.
	 *
	 * @return $this
	 */
	protected function send_client_secret() {
		$this->body['client_secret'] = $this->client_secret;
		return $this;
	}

	/**
	 * Set a header array key to a specific value.
	 *
	 * @param string $header - Header name to set.
	 * @param string $value - Value to set to the key above.
	 *
	 * @return $this
	 */
	protected function add_header( $header, $value ) {
		$this->headers[ $header ] = $value;
		return $this;
	}

	/**
	 * Set a body array key to a specific value.
	 *
	 * @param string $key - Body key to set.
	 * @param string $value - Value to set to the key above.
	 *
	 * @return $this
	 */
	protected function add_body( $key, $value ) {
		$this->body[ $key ] = $value;
		return $this;
	}

	/**
	 * Return the remote URL from the domain and path.
	 *
	 * @return string
	 */
	protected function build_url() {
		return 'https://' . $this->domain . '/' . $this->remote_path;
	}

	/**
	 * Send a GET request.
	 *
	 * @return $this
	 */
	protected function get() {
		return $this->request( 'GET' );
	}

	/**
	 * Send a POST request.
	 *
	 * @return $this
	 */
	protected function post() {
		return $this->add_header( 'Content-Type', 'application/json' )->request( 'POST' );
	}

	/**
	 * Send a DELETE request.
	 *
	 * @return $this
	 */
	protected function delete() {
		return $this->request( 'DELETE' );
	}

	/**
	 * Send a PATCH request.
	 *
	 * @return $this
	 */
	protected function patch() {
		return $this->add_header( 'Content-Type', 'application/json' )->request( 'PATCH' );
	}

	/**
	 * Handle a WP_Error stemming from a failed HTTP call.
	 * Can be called in child class handle_response method to generically handle WP_Error responses.
	 *
	 * @param string $method - Method name that called the API.
	 *
	 * @return bool - True if there was a WP_Error, false if not.
	 */
	protected function handle_wp_error( $method ) {
		if ( $this->response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( $method, $this->response );
			return true;
		}
		return false;
	}

	/**
	 * Handle common failure responses returned from a remote server.
	 * Can be called in child class handle_response method to generically handle common failure responses.
	 *
	 * @param string $method - Method name that called the API.
	 * @param int    $success_code - Code integer representing success.
	 *
	 * @return bool - True if there was an error, false if not.
	 */
	protected function handle_failed_response( $method, $success_code = 200 ) {

		if ( $this->response_code === $success_code ) {
			return false;
		}

		$response_body = json_decode( $this->response_body, true );
		$message       = __( 'Error returned', 'wp-auth0' );

		if ( isset( $response_body['statusCode'] ) ) {

			if ( isset( $response_body['message'] ) ) {
				$message .= ' - ' . sanitize_text_field( $response_body['message'] );
			}
			if ( isset( $response_body['errorCode'] ) ) {
				$message .= ' [' . sanitize_text_field( $response_body['errorCode'] ) . ']';
			}
			WP_Auth0_ErrorManager::insert_auth0_error( $method, new WP_Error( $response_body['statusCode'], $message ) );
			return true;
		}

		if ( isset( $response_body['error'] ) ) {
			if ( isset( $response_body['error_description'] ) ) {
				$message .= ' - ' . sanitize_text_field( $response_body['error_description'] );
			}
			WP_Auth0_ErrorManager::insert_auth0_error( $method, new WP_Error( $response_body['error'], $message ) );
			return true;
		}

		WP_Auth0_ErrorManager::insert_auth0_error( $method, $this->response_body );
		return true;
	}

	/**
	 * Decode an RS256 Auth0 Management API token.
	 *
	 * @deprecated - 3.10.0, not used.
	 *
	 * @param string $token - API JWT to decode.
	 *
	 * @return object
	 *
	 * @throws DomainException              Algorithm was not provided.
	 * @throws UnexpectedValueException     Provided JWT was invalid.
	 * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed.
	 * @throws BeforeValidException         Provided JWT used before it's eligible as defined by 'nbf'.
	 * @throws BeforeValidException         Provided JWT used before it's been created as defined by 'iat'.
	 * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	protected function decode_jwt( $token ) {
		return JWT::decode(
			$token,
			WP_Auth0_Api_Client::JWKfetch( $this->domain ),
			array( 'RS256' )
		);
	}

	/**
	 * Send the HTTP request.
	 *
	 * @param string $method - HTTP method to use.
	 *
	 * @return $this
	 *
	 * @codeCoverageIgnore - Tested by individual HTTP methods in TestApiAbstract::testHttpRequests()
	 */
	private function request( $method ) {
		$remote_url = $this->build_url();
		$http_args  = array(
			'headers' => $this->headers,
			'method'  => $method,
			'body'    => ! empty( $this->body ) ? json_encode( $this->body ) : null,
		);

		$this->response      = wp_remote_request( $remote_url, $http_args );
		$this->response_code = (int) wp_remote_retrieve_response_code( $this->response );
		$this->response_body = wp_remote_retrieve_body( $this->response );

		return $this;
	}

	/**
	 * Remove slash at the first character, if there is one.
	 *
	 * @param string $path - Path to clean.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	private function clean_path( $path ) {
		if ( ! empty( $path[0] ) && '/' === $path[0] ) {
			$path = substr( $path, 1 );
		}
		return $path;
	}
}
