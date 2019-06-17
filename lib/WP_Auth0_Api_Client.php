<?php

class WP_Auth0_Api_Client {

	const DEFAULT_CLIENT_ALG = 'RS256';

	private static $connect_info = null;

	/**
	 * Generate the API endpoint with a provided domain
	 *
	 * @since 3.5.0
	 *
	 * @param string $path - API path appended to the domain
	 * @param string $domain - domain to use, blank uses default
	 *
	 * @return string
	 */
	private static function get_endpoint( $path = '', $domain = '' ) {

		if ( empty( $domain ) ) {
			$a0_options = WP_Auth0_Options::Instance();
			$domain     = $a0_options->get( 'domain' );
		}

		if ( ! empty( $path[0] ) && '/' === $path[0] ) {
			$path = substr( $path, 1 );
		}

		return "https://{$domain}/{$path}";
	}

	/**
	 * Return basic connection information, or a specific value
	 *
	 * @since 3.5.0
	 *
	 * @param string $opt - specific option needed, returns all if blank
	 *
	 * @return string|array
	 */
	public static function get_connect_info( $opt = '' ) {

		if ( is_null( self::$connect_info ) ) {
			$a0_options = WP_Auth0_Options::Instance();

			self::$connect_info = [
				'domain'                => $a0_options->get( 'domain' ),
				'client_id'             => $a0_options->get( 'client_id' ),
				'client_secret'         => $a0_options->get( 'client_secret' ),
				'client_secret_encoded' => $a0_options->get( 'client_secret_b64_encoded' ),
				'connection'            => $a0_options->get( 'db_connection_name' ),
				'app_token'             => null,
				'audience'              => self::get_endpoint( 'api/v2/' ),
			];
		}

		if ( empty( $opt ) ) {
			return self::$connect_info;
		} else {
			return ! empty( self::$connect_info[ $opt ] ) ? self::$connect_info[ $opt ] : '';
		}
	}

	/**
	 * Get required telemetry header.
	 *
	 * @deprecated - 3.10.0, not used.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function get_info_headers() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return WP_Auth0_Api_Abstract::get_info_headers();
	}

	/**
	 * Basic header components for an Auth0 API call
	 *
	 * @since 3.5.0
	 *
	 * @param string $token - for Authorization header
	 * @param string $content_type - for Content-Type header
	 *
	 * @return array
	 */
	private static function get_headers( $token = '', $content_type = 'application/json' ) {

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		if ( ! empty( $token ) ) {
			$headers['Authorization'] = "Bearer {$token}";
		}

		if ( ! empty( $content_type ) ) {
			$headers['Content-Type'] = $content_type;
		}

		return $headers;
	}

	/**
	 * @deprecated - 3.11.0, use WP_Auth0_Api_Client_Credentials instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function get_token( $domain, $client_id, $client_secret, $grantType = 'client_credentials', $extraBody = null ) {
		if ( ! is_array( $extraBody ) ) {
			$body = [];
		} else {
			$body = $extraBody;
		}

		$endpoint = "https://$domain/";

		$body['client_id']     = $client_id;
		$body['client_secret'] = is_null( $client_secret ) ? '' : $client_secret;
		$body['grant_type']    = $grantType;

		$headers                 = WP_Auth0_Api_Abstract::get_info_headers();
		$headers['content-type'] = 'application/x-www-form-urlencoded';

		$response = wp_remote_post(
			$endpoint . 'oauth/token',
			[
				'headers' => $headers,
				'body'    => $body,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		return $response;
	}

	/**
	 * Get a client_credentials token using default stored connection info
	 *
	 * @deprecated - 3.10.0, not used.
	 *
	 * @since 3.4.1
	 *
	 * @return bool|string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function get_client_token() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$response = wp_remote_post(
			self::get_endpoint( 'oauth/token' ),
			[
				'headers' => self::get_headers(),
				'body'    => json_encode(
					[
						'client_id'     => self::get_connect_info( 'client_id' ),
						'client_secret' => self::get_connect_info( 'client_secret' ),
						'audience'      => self::get_connect_info( 'audience' ),
						'grant_type'    => 'client_credentials',
					]
				),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] !== 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		$response = json_decode( $response['body'] );

		return ! empty( $response->access_token ) ? $response->access_token : '';
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @param string $domain - tenant domain
	 * @param string $access_token - access token with at least `openid` scope
	 *
	 * @return array|WP_Error
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function get_user_info( $domain, $access_token ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return wp_remote_get(
			self::get_endpoint( 'userinfo', $domain ),
			[ 'headers' => self::get_headers( $access_token ) ]
		);
	}

	/**
	 * @deprecated - 3.11.0, use WP_Auth0_Api_Get_User instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function get_user( $domain, $jwt, $user_id ) {
		$endpoint = "https://$domain/api/v2/users/" . urlencode( $user_id );

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";

		return wp_remote_get(
			$endpoint,
			[
				'headers' => $headers,
			]
		);

	}

	public static function signup_user( $domain, $data ) {

		$endpoint = "https://$domain/dbconnections/signup";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['content-type'] = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'headers' => $headers,
				'body'    => json_encode( $data ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] !== 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function get_required_scopes() {
		return [
			'read:users',
			'update:users',
		];
	}

	/**
	 * Get a single client via the Management API
	 *
	 * @deprecated - 3.10.0, not used.
	 *
	 * @see https://auth0.com/docs/api/management/v2#!/Clients/get_clients_by_id
	 *
	 * @param string $app_token - an app token for the management API with read:clients scope
	 * @param string $client_id - a valid client ID in the same tenant as the app token
	 *
	 * @return array|bool|mixed|object
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function get_client( $app_token, $client_id ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$response = wp_remote_get(
			self::get_endpoint( '/api/v2/clients/' . urlencode( $client_id ) ),
			[
				'headers' => self::get_headers( $app_token ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Create a new client for the WordPress site
	 *
	 * @see https://auth0.com/docs/clients/client-settings/regular-web-app
	 * @see https://auth0.com/docs/api/management/v2#!/Clients/post_clients
	 *
	 * @param string $domain - domain to use with the app_token provided
	 * @param string $app_token - app token for the Management API
	 * @param string $name - name of the new client
	 *
	 * @return bool|object|array
	 */
	public static function create_client( $domain, $app_token, $name ) {

		$options = WP_Auth0_Options::Instance();

		$payload = [
			'name'                => $name,
			'app_type'            => 'regular_web',

			// Callback URLs for Auth Code and Hybrid/Implicit
			'callbacks'           => [
				$options->get_wp_auth0_url(),
			],

			// Web origins do not take into account the path
			'web_origins'         => $options->get_web_origins(),

			// Force SSL, will not work without it
			'cross_origin_loc'    => $options->get_cross_origin_loc(),
			'cross_origin_auth'   => true,

			// A set of URLs that are valid to redirect to after logout from Auth0
			'allowed_logout_urls' => [
				home_url(),
				wp_login_url(),
			],

			// Advanced > Grant Types
			'grant_types'         => self::get_client_grant_types(),

			// Advanced > OAuth > JsonWebToken Signature Algorithm
			'jwt_configuration'   => [
				'alg' => self::DEFAULT_CLIENT_ALG,
			],

			// "Use Auth0 to do Single Sign On"
			'sso'                 => true,

			// Advanced > OAuth > OIDC Conformant
			// https://auth0.com/docs/api-auth/intro#legacy-vs-new
			'oidc_conformant'     => true,
		];

		$response = wp_remote_post(
			self::get_endpoint( 'api/v2/clients', $domain ),
			[
				'headers' => self::get_headers( $app_token ),
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response->get_error_message() );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function update_client( $domain, $app_token, $client_id, $sso, $payload = [] ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/clients/$client_id";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'PATCH',
				'headers' => $headers,
				'body'    => json_encode( array_merge( [ 'sso' => (bool) $sso ], $payload ) ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function create_rule( $domain, $app_token, $name, $script, $enabled = true ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$payload = [
			'name'    => $name,
			'script'  => $script,
			'enabled' => $enabled,
			'stage'   => 'login_success',
		];

		$endpoint = "https://$domain/api/v2/rules";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' ' . $name, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__ . ' ' . $name, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function delete_rule( $domain, $app_token, $id ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/rules/$id";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'DELETE',
				'headers' => $headers,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 204 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Create a client grant for the management API
	 *
	 * @param $app_token
	 * @param $client_id
	 *
	 * @return array|bool|mixed|object
	 */
	public static function create_client_grant( $app_token, $client_id ) {

		$data = [
			'client_id' => $client_id,
			'audience'  => self::get_connect_info( 'audience' ),
			'scope'     => self::get_required_scopes(),
		];

		$response = wp_remote_post(
			self::get_endpoint( 'api/v2/client-grants' ),
			[
				'headers' => self::get_headers( $app_token ),
				'body'    => json_encode( $data ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( 409 === $response['response']['code'] ) {

			// Client grant from WP-created client to Management API already exists
			WP_Auth0_ErrorManager::insert_auth0_error(
				__METHOD__,
				sprintf(
					// translators: placeholders are machine names stored for this WP instance and must be included.
					__( 'A client grant for %1$s to %2$s already exists. Make sure this grant at least includes %3$s.', 'wp-auth0' ),
					self::get_connect_info( 'client_id' ),
					self::get_connect_info( 'audience' ),
					implode( ', ', self::get_required_scopes() )
				)
			);

			return json_decode( $response['body'] );

		} elseif ( $response['response']['code'] != 201 ) {

			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function create_connection( $domain, $app_token, $payload ) {
		$endpoint = "https://$domain/api/v2/connections";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function search_connection( $domain, $app_token, $strategy = null ) {
		$endpoint = "https://$domain/api/v2/connections";

		if ( $strategy ) {
			$endpoint .= "?strategy=$strategy";
		}

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";

		$response = wp_remote_get(
			$endpoint,
			[
				'headers' => $headers,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function get_connection( $domain, $app_token, $id ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$endpoint = "https://$domain/api/v2/connections/$id";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";

		$response = wp_remote_get(
			$endpoint,
			[
				'headers' => $headers,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Update a Connection via the Management API.
	 * Note: $payload must be a complete settings object, not just the property to change.
	 *
	 * @param string   $domain - Auth0 Domain.
	 * @param string   $app_token - Valid Auth0 Management API token.
	 * @param string   $id - DB Connection ID.
	 * @param stdClass $payload - DB Connection settings, will override existing.
	 *
	 * @return bool|object
	 */
	public static function update_connection( $domain, $app_token, $id, $payload ) {
		$endpoint = "https://$domain/api/v2/connections/$id";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		unset( $payload->name );
		unset( $payload->strategy );
		unset( $payload->id );

		if ( ! empty( $payload->enabled_clients ) ) {
			$payload->enabled_clients = array_values( $payload->enabled_clients );
		}

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'PATCH',
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static function update_user( $domain, $app_token, $id, $payload ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$endpoint = "https://$domain/api/v2/users/$id";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'PATCH',
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Return the Management API scopes needed for install.
	 *
	 * @return array
	 */
	public static function ConsentRequiredScopes() {
		return [
			'create:clients',
			'create:client_grants',
			'update:connections',
			'create:connections',
			'read:connections',
			'read:users',
			'update:users',
		];
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - To be deprecated
	 */
	public static function GetConsentScopestoShow() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$scopes    = self::ConsentRequiredScopes();
		$grouped   = [];
		$processed = [];

		foreach ( $scopes as $scope ) {
			list($action, $resource) = explode( ':', $scope );
			$grouped[ $resource ][]  = $action;
		}
		foreach ( $grouped as $resource => $actions ) {
			$str = '';

			sort( $actions );

			for ( $a = 0; $a < count( $actions ); $a++ ) {
				if ( $a > 0 ) {
					if ( $a === count( $actions ) - 1 ) {
						$str .= ' and ';
					} else {
						$str .= ', ';
					}
				}
				$str .= $actions[ $a ];
			}

			$processed[ $resource ] = $str;
		}
		return $processed;
	}

	/**
	 * @deprecated - 3.10.0, not used.
	 *
	 * @codeCoverageIgnore - To be deprecated
	 */
	public static function update_guardian( $domain, $app_token, $factor, $enabled ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$endpoint = "https://$domain/api/v2/guardian/factors/$factor";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$payload = [
			'enabled' => $enabled,
		];

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'PUT',
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Convert a certificate to PEM format
	 *
	 * @see https://en.wikipedia.org/wiki/Privacy-enhanced_Electronic_Mail
	 *
	 * @param string $cert - certificate, like from .well-known/jwks.json
	 *
	 * @return string
	 */
	protected static function convertCertToPem( $cert ) {
		return '-----BEGIN CERTIFICATE-----' . PHP_EOL
			   . chunk_split( $cert, 64, PHP_EOL )
			   . '-----END CERTIFICATE-----' . PHP_EOL;
	}

	public static function JWKfetch( $domain ) {

		$a0_options = WP_Auth0_Options::Instance();

		$endpoint = "https://$domain/.well-known/jwks.json";

		if ( false === ( $secret = get_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME ) ) ) {

			$secret = [];

			$response = wp_remote_get( $endpoint, [] );

			if ( $response instanceof WP_Error ) {
				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
				error_log( $response->get_error_message() );
				return false;
			}

			if ( $response['response']['code'] != 200 ) {
				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
				error_log( $response['body'] );
				return false;
			}

			if ( $response['response']['code'] >= 300 ) {
				return false;
			}

			$jwks = json_decode( $response['body'], true );

			foreach ( $jwks['keys'] as $key ) {
				$secret[ $key['kid'] ] = self::convertCertToPem( $key['x5c'][0] );
			}

			if ( $cache_expiration = $a0_options->get( 'cache_expiration' ) ) {
				set_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME, $secret, $cache_expiration * MINUTE_IN_SECONDS );
			}
		}

		return $secret;
	}

	/**
	 * Return the grant types needed for new clients
	 *
	 * @return array
	 */
	public static function get_client_grant_types() {

		return [
			'authorization_code',
			'implicit',
			'refresh_token',
			'client_credentials',
		];
	}

	/*
	 *
	 * DEPRECATED
	 *
	 */

	/**
	 * Deprecated to conform to OIDC standards
	 *
	 * @see https://auth0.com/docs/api-auth/intro#other-authentication-api-endpoints
	 *
	 * @deprecated - 3.6.0, not used and no replacement provided.
	 *
	 * @param $domain
	 * @param $client_id
	 * @param $username
	 * @param $password
	 * @param $connection
	 * @param $scope
	 *
	 * @return array|bool|mixed|object
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function ro( $domain, $client_id, $username, $password, $connection, $scope ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/";

		$headers                 = WP_Auth0_Api_Abstract::get_info_headers();
		$headers['content-type'] = 'application/x-www-form-urlencoded';
		$body                    = [
			'client_id'  => $client_id,
			'username'   => $username,
			'password'   => $password,
			'connection' => $connection,
			'grant_type' => 'password',
			'scope'      => $scope,
		];

		$response = wp_remote_post(
			$endpoint . 'oauth/ro',
			[
				'headers' => $headers,
				'body'    => $body,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );

	}

	/**
	 * Validate the scopes of the API token.
	 *
	 * @deprecated - 3.8.0, not used and no replacement needed.
	 *
	 * @param string $app_token - API token.
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function validate_user_token( $app_token ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		if ( empty( $app_token ) ) {
			return false;
		} else {
			$parts = explode( '.', $app_token );

			if ( count( $parts ) !== 3 ) {
				return false;
			} else {
				$payload = json_decode( JWT::urlsafeB64Decode( $parts[1] ) );

				if ( ! isset( $payload->scope ) ) {
					return false;
				} else {
					$required_scopes = self::get_required_scopes();
					$token_scopes    = explode( ' ', $payload->scope );
					$intersect       = array_intersect( $required_scopes, $token_scopes );

					if ( count( $intersect ) != count( $required_scopes ) ) {
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * @deprecated - 3.8.0, not used.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function search_users( $domain, $jwt, $q = '', $page = 0, $per_page = 100, $include_totals = false, $sort = 'user_id:1' ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$include_totals = $include_totals ? 'true' : 'false';

		$endpoint = "https://$domain/api/v2/users?include_totals=$include_totals&per_page=$per_page&page=$page&sort=$sort&q=$q&search_engine=v2";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";

		$response = wp_remote_get(
			$endpoint,
			[
				'headers' => $headers,
			]
		);

		return json_decode( $response['body'] );
	}

	/**
	 * Trigger a verification email re-send.
	 *
	 * @deprecated - 3.8.0, use WP_Auth0_Api_Jobs_Verification instead.
	 *
	 * @since 3.5.0
	 *
	 * @param string $user_id - Auth0 formatted user_id
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function resend_verification_email( $user_id ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$response = wp_remote_post(
			self::get_endpoint( 'api/v2/jobs/verification-email' ),
			[
				'headers' => self::get_headers( self::get_client_token() ),
				'body'    => json_encode(
					[
						'user_id'   => $user_id,
						'client_id' => self::get_connect_info( 'client_id' ),
					]
				),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] !== 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return true;
	}

	/**
	 * @deprecated - 3.8.0, use self::signup_user() instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function create_user( $domain, $jwt, $data ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/users";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'headers' => $headers,
				'body'    => json_encode( $data ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement needed.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function search_clients( $domain, $app_token ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/clients";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";

		$response = wp_remote_get(
			$endpoint,
			[
				'headers' => $headers,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function get_current_user( $domain, $app_token ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		list( $head, $payload, $signature ) = explode( '.', $app_token );
		$decoded                            = json_decode( JWT::urlsafeB64Decode( $payload ) );

		return self::get_user( $domain, $app_token, $decoded->sub );
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function delete_connection( $domain, $app_token, $id ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/connections/$id";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'DELETE',
				'headers' => $headers,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 204 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.8.0, use WP_Auth0_Api_Delete_User_Mfa instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function delete_user_mfa( $domain, $app_token, $user_id, $provider ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/users/$user_id/multifactor/$provider";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'DELETE',
				'headers' => $headers,
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 204 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.8.0, use WP_Auth0_Api_Change_Password instead.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function change_password( $domain, $payload ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/dbconnections/change_password";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['content-type'] = 'application/json';

		$response = wp_remote_post(
			$endpoint,
			[
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public static function link_users( $domain, $app_token, $main_user_id, $user_id, $provider, $connection_id = null ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$endpoint = "https://$domain/api/v2/users/$main_user_id/identities";

		$headers = WP_Auth0_Api_Abstract::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type']  = 'application/json';

		$payload             = [];
		$payload['provider'] = $provider;
		$payload['user_id']  = $user_id;
		if ( $connection_id ) {
			$payload['connection_id'] = $connection_id;
		}

		$response = wp_remote_post(
			$endpoint,
			[
				'headers' => $headers,
				'body'    => json_encode( $payload ),
			]
		);

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) {
			return false;
		}

		return json_decode( $response['body'] );
	}
}
