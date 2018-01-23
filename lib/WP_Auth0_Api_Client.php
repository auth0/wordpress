<?php

class WP_Auth0_Api_Client {

	private static $connect_info = null;

	/**
	 * Generate the API endpoint with a provided domain
	 *
	 * @since 3.4.1
	 *
	 * @param string $path - API path appended to the domain
	 * @param string $domain - domain to use, blank uses default
	 *
	 * @return string
	 */
	private static function get_endpoint( $path = '', $domain = '' ) {

		if ( empty( $domain ) ) {
			$a0_options = WP_Auth0_Options::Instance();
			$domain = $a0_options->get( 'domain' );
		}

		if ( ! empty( $path[0] ) && '/' === $path[0] ) {
			$path = substr( $path, 1 );
		}

		return "https://{$domain}/{$path}";
	}

	/**
	 * Return basic connection information, or a specific value
	 *
	 * @since 3.4.1
	 *
	 * @param string $opt - specific option needed, returns all if blank
	 *
	 * @return string|array
	 */
	public static function get_connect_info( $opt = '' ) {

		if ( is_null( self::$connect_info ) ) {
			$a0_options = WP_Auth0_Options::Instance();

			self::$connect_info = array(
				'domain' => $a0_options->get( 'domain' ),
				'client_id' => $a0_options->get( 'client_id' ),
				'client_secret' => $a0_options->get( 'client_secret' ),
				'connection' => $a0_options->get( 'db_connection_name' ),
				'audience' => $a0_options->get( 'auth0_app_token_audience' ),
			);

			if ( empty( self::$connect_info[ 'audience' ] ) ) {
				self::$connect_info[ 'audience' ] = self::get_endpoint( 'api/v2/' );
			}
		}

		if ( empty( $opt ) ) {
			return self::$connect_info;
		} else {
			return ! empty( self::$connect_info[ $opt ] ) ? self::$connect_info[ $opt ] : '';
		}
	}

	public static function ro( $domain, $client_id, $username, $password, $connection, $scope ) {

		$endpoint = "https://$domain/";

		$headers = self::get_info_headers();
		$headers['content-type'] = 'application/x-www-form-urlencoded';
		$body = array(
			'client_id' => $client_id,
			'username' => $username,
			'password' => $password,
			'connection' => $connection,
			'grant_type' => 'password',
			'scope' => $scope
		);

		$response = wp_remote_post( $endpoint . 'oauth/ro', array(
				'headers' => $headers,
				'body' => $body,
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::ro', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::ro', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );

	}

	public static function validate_user_token( $app_token ) {

		if ( empty( $app_token ) ) {
			return false;
		} else {
			$parts = explode( '.', $app_token );

			if ( count( $parts ) !== 3 ) {
				return false;
			} else {
				$payload = json_decode( JWT::urlsafeB64Decode( $parts[1] ) );

				if ( !isset( $payload->scope ) ) {
					return false;
				} else {
					$required_scopes = self::get_required_scopes();
					$token_scopes = explode( ' ', $payload->scope );
					$intersect = array_intersect( $required_scopes, $token_scopes );

					if ( count( $intersect ) != count( $required_scopes ) ) {
						return false;
					}
				}

			}
		}
		return true;
	}

	public static function get_info_headers() {
		global $wp_version;

		$a0_options = WP_Auth0_Options::Instance();

		if ( $a0_options->get( 'metrics' ) != 1 ) {
			return array();
		}

		return array(
			'Auth0-Client' => base64_encode( wp_json_encode( array(
						'name' => 'wp-auth0',
						'version' => WPA0_VERSION,
						'environment' => array(
							'PHP' => phpversion(),
							'WordPress' => $wp_version,
						)
					) ) )
		);
	}

	/**
	 * Basic header components for an Auth0 API call
	 *
	 * @since 3.4.1
	 *
	 * @param string $token - for Authorization header
	 * @param string $content_type - for Content-Type header
	 *
	 * @return array
	 */
	private static function get_headers( $token = '', $content_type = 'application/json' ) {

		$headers = self::get_info_headers();

		if ( ! empty( $token ) ) {
			$headers['Authorization'] = "Bearer {$token}";
		}

		if ( ! empty( $content_type ) ) {
			$headers[ 'Content-Type' ] = $content_type;
		}

		return $headers;
	}

	public static function get_token( $domain, $client_id, $client_secret, $grantType = 'client_credentials', $extraBody = null ) {
		if ( ! is_array( $extraBody ) ) {
			$body = array();
		} else {
			$body = $extraBody;
		}

		$endpoint = "https://$domain/";

		$body['client_id'] = $client_id;
		$body['client_secret'] = is_null( $client_secret ) ? '' : $client_secret;
		$body['grant_type'] = $grantType;

		$headers = self::get_info_headers();
		$headers['content-type'] = 'application/x-www-form-urlencoded';


		$response = wp_remote_post( $endpoint . 'oauth/token', array(
				'headers' => $headers,
				'body' => $body,
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::get_token', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		return $response;
	}

	public static function get_user_info( $domain, $access_token ) {

		$endpoint = "https://$domain/";

		$headers = self::get_info_headers();
		$headers['Authorization'] = "Bearer $access_token";

		return wp_remote_get( $endpoint . 'userinfo/' , array(
				'headers' => $headers,
			) );

	}

	public static function search_users( $domain, $jwt, $q = "", $page = 0, $per_page = 100, $include_totals = false, $sort = "user_id:1" ) {

		$include_totals = $include_totals ? 'true' : 'false';

		$endpoint = "https://$domain/api/v2/users?include_totals=$include_totals&per_page=$per_page&page=$page&sort=$sort&q=$q&search_engine=v2";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";

		$response = wp_remote_get( $endpoint  , array(
				'headers' => $headers,
			) );

		return json_decode( $response['body'] );
	}

	/**
	 * Trigger a verification email re-send
	 *
	 * @since 3.4.1
	 *
	 * @param $access_token
	 * @param $user_id
	 *
	 * @return bool
	 */
	public static function resend_verification_email( $access_token, $user_id ) {

		$response = wp_remote_post(
			self::get_endpoint( 'api/v2/jobs/verification-email' ),
			array(
				'headers' => self::get_headers( $access_token ),
				'body' => json_encode( array(
					'user_id' => $user_id,
					'client_id' => self::get_connect_info( 'client_id' ),
				) ),
			)
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

	public static function get_user( $domain, $jwt, $user_id ) {
		$endpoint = "https://$domain/api/v2/users/" . urlencode( $user_id );

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";

		return wp_remote_get( $endpoint  , array(
				'headers' => $headers,
			) );
	}

	public static function create_user( $domain, $jwt, $data ) {

		$endpoint = "https://$domain/api/v2/users";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";
		$headers['content-type'] = 'application/json';

		$response = wp_remote_post( $endpoint  , array(
				'headers' => $headers,
				'body' => json_encode( $data )
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_user', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_user', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function signup_user( $domain, $data ) {

		$endpoint = "https://$domain/dbconnections/signup";

		$headers = self::get_info_headers();

		$headers['content-type'] = 'application/json';

		$response = wp_remote_post( $endpoint  , array(
			'headers' => $headers,
			'body' => json_encode( $data )
		) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::signup_user', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] !== 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::signup_user', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function get_required_scopes() {
		return array(
			'update:clients',
			'update:connections',
			'create:connections',
			'read:connections',
			'create:rules',
			'delete:rules',
			'update:users',
			'update:guardian_factors',
		);
	}

	public static function create_client( $domain, $app_token, $name ) {

		$endpoint = "https://$domain/api/v2/clients";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'POST',
				'headers' => $headers,
				'body' => json_encode( array(
						'name' => $name,
						'callbacks' => array(
							site_url( 'index.php?auth0=1' ),
							wp_login_url()
						),
						"allowed_origins"=>array(
							wp_login_url()
						),
						"jwt_configuration" => array(
							"alg" => "RS256"
						),
						"app_type" => "regular_web",
						"cross_origin_auth" => true,
						"cross_origin_loc" => site_url('index.php?auth0fallback=1','https'),
						"allowed_logout_urls" => array( wp_logout_url() ),
					) )
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_client', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_client', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		$response = json_decode( $response['body'] );
	
		// Workaround: Can't add `web_origin` on create
		$payload = array(
			"web_origins" => ( home_url() === site_url() ? array( home_url() ) : array( home_url(), site_url() ) )
		);
		$updateResponse = WP_Auth0_Api_Client::update_client($domain, $app_token, $response->client_id, false, $payload);

		if ( $updateResponse instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_client', $updateResponse );
			error_log( $updateResponse->get_error_message() );
			return false;
		}

		return $response;
	}

	public static function search_clients( $domain, $app_token ) {
		$endpoint = "https://$domain/api/v2/clients";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";

		$response =  wp_remote_get( $endpoint  , array(
				'headers' => $headers,
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::search_clients', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::search_clients', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode( $response['body'] );
	}

	public static function update_client( $domain, $app_token, $client_id, $sso, $payload = array() ) {

		$endpoint = "https://$domain/api/v2/clients/$client_id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'PATCH',
				'headers' => $headers,
				'body' => json_encode( array_merge(array( 'sso' => boolval($sso)), $payload) )
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_client', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_client', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function create_rule( $domain, $app_token, $name, $script, $enabled = true ) {
		$payload = array(
			"name" => $name,
			"script" => $script,
			"enabled" => $enabled,
			"stage" => "login_success"
		);

		$endpoint = "https://$domain/api/v2/rules";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'POST',
				'headers' => $headers,
				'body' => json_encode( $payload )
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_rule ' . $name, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_rule ' . $name, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function delete_rule( $domain, $app_token, $id ) {

		$endpoint = "https://$domain/api/v2/rules/$id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'DELETE',
				'headers' => $headers
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::delete_rule', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 204 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::delete_rule', $response['body'] );
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

		$data = array(
			'client_id' => $client_id,
			'audience' => self::get_connect_info( 'audience' ),
			'scope' => self::ConsentRequiredScopes()
		);

		$response = wp_remote_post( self::get_endpoint( 'api/v2/client-grants' ), array(
			'headers' => self::get_headers( $app_token ),
			'body' => json_encode( $data ),
		) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error(  __METHOD__, $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public static function create_connection( $domain, $app_token, $payload ) {
		$endpoint = "https://$domain/api/v2/connections";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'POST',
				'headers' => $headers,
				'body' => json_encode( $payload )
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_connection', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::create_connection', $response['body'] );
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

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";

		$response =  wp_remote_get( $endpoint  , array(
				'headers' => $headers,
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::search_connection', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::search_connection', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode( $response['body'] );
	}

	public static function get_connection( $domain, $app_token, $id ) {
		$endpoint = "https://$domain/api/v2/connections/$id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";

		$response =  wp_remote_get( $endpoint  , array(
				'headers' => $headers,
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::get_connection', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::get_connection', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode( $response['body'] );
	}

	public static function get_current_user( $domain, $app_token ) {
		list( $head, $payload, $signature ) = explode( '.', $app_token );
		$decoded = json_decode( JWT::urlsafeB64Decode( $payload) );

		return self::get_user($domain, $app_token, $decoded->sub);
	}

	public static function update_connection($domain, $app_token, $id, $payload) {
		$endpoint = "https://$domain/api/v2/connections/$id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'PATCH',
				'headers' => $headers,
				'body' => json_encode($payload)
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_connection', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_connection', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode($response['body']);
	}

	public static function delete_connection($domain, $app_token, $id) {
		$endpoint = "https://$domain/api/v2/connections/$id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'DELETE',
				'headers' => $headers
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::delete_connection', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 204 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::delete_connection', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode($response['body']);
	}

	public static function delete_user_mfa($domain, $app_token, $user_id, $provider) {

		$endpoint = "https://$domain/api/v2/users/$user_id/multifactor/$provider";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'DELETE',
				'headers' => $headers
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::delete_user_mfa', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 204 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::delete_user_mfa', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		return json_decode($response['body']);
	}

	public static function update_user($domain, $app_token, $id, $payload) {
		$endpoint = "https://$domain/api/v2/users/$id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'PATCH',
				'headers' => $headers,
				'body' => json_encode($payload)
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_users', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_users', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode($response['body']);
	}

	public static function change_password($domain, $payload) {
		$endpoint = "https://$domain/dbconnections/change_password";

		$headers = self::get_info_headers();

		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'POST',
				'headers' => $headers,
				'body' => json_encode($payload)
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::change_password', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::change_password', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode($response['body']);
	}

	public static function link_users($domain, $app_token, $main_user_id, $user_id, $provider, $connection_id = null) {
		$endpoint = "https://$domain/api/v2/users/$main_user_id/identities";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$payload = array();
		$payload['provider'] = $provider;
		$payload['user_id'] = $user_id;
		if ($connection_id) {
			$payload['connection_id'] = $connection_id;
		}

		$response = wp_remote_post( $endpoint  , array(
				'headers' => $headers,
				'body' => json_encode($payload)
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::link_users', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 201 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::link_users', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode($response['body']);
	}

	public static function ConsentRequiredScopes() {
		return array(
			'create:clients',
			'update:clients',

			'update:connections',
			'create:connections',
			'read:connections',

			'create:rules',
			'delete:rules',

			'read:users',
			'update:users',
			'create:users',

			'update:guardian_factors',
		);
	}

	public static function GetConsentScopestoShow() {
		$scopes = self::ConsentRequiredScopes();
		$grouped = array();
		$processed = array();

		foreach ($scopes as $scope) {
			list($action, $resource) = explode(":", $scope);
			$grouped[$resource][] = $action;
		}
		foreach ($grouped as $resource => $actions) {
			$str = "";

			sort($actions);

			for ($a = 0; $a < count($actions); $a++) {
				if ($a > 0) {
					if ($a === count($actions) - 1) {
						$str .= ' and ';
					} else {
						$str .= ', ';
					}
				}
				$str .= $actions[$a];
			}

			$processed[$resource] = $str;
		}
		return $processed;
	}

	public static function update_guardian($domain, $app_token, $factor, $enabled) {
		$endpoint = "https://$domain/api/v2/guardian/factors/$factor";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$payload = array(
			"enabled" => $enabled
		);

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'PUT',
				'headers' => $headers,
				'body' => json_encode($payload)
			) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_guardian', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::update_guardian', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode($response['body']);
	}

  protected function convertCertToPem($cert) {
      return '-----BEGIN CERTIFICATE-----'.PHP_EOL
          .chunk_split($cert, 64, PHP_EOL)
          .'-----END CERTIFICATE-----'.PHP_EOL;
  }

  public static function JWKfetch($domain) {

	$a0_options = WP_Auth0_Options::Instance();

	$endpoint = "https://$domain/.well-known/jwks.json";

    $cache_expiration = $a0_options->get('cache_expiration');

	if ( false === ($secret = get_transient('WP_Auth0_JWKS_cache') ) ) {

		$secret = [];

		$response = wp_remote_get( $endpoint, array() );

		if ( $response instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::JWK_fetch', $response );
			error_log( $response->get_error_message() );
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'WP_Auth0_Api_Client::JWK_fetch', $response['body'] );
			error_log( $response['body'] );
			return false;
		}

		if ( $response['response']['code'] >= 300 ) return false;		

		$jwks = json_decode($response['body'], true);

		foreach ($jwks['keys'] as $key) { 
			$secret[$key['kid']] = self::convertCertToPem($key['x5c'][0]);
		}

		if ($cache_expiration !== 0) {
			set_transient( 'WP_Auth0_JWKS_cache', $secret, $cache_expiration * MINUTE_IN_SECONDS );
		}

	}

  return $secret;
  }
}