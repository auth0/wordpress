<?php

class WP_Auth0_Api_Client {

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
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'POST',
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

		$logout_url = home_url();

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'POST',
				'headers' => $headers,
				'body' => json_encode( array(
						'name' => $name,
						'callbacks' => array(
							home_url( '/index.php?auth0=1' ),
							home_url( '/wp-login.php' )
						),
						"allowed_origins"=>array(
							home_url( '/wp-login.php' ),
							admin_url( '/admin.php?page=wpa0-setup&step=2&profile=social' )
						),
						"allowed_logout_urls" => array(
							$logout_url
						),
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

		return json_decode( $response['body'] );
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

	public static function update_client( $domain, $app_token, $client_id, $sso ) {

		$endpoint = "https://$domain/api/v2/clients/$client_id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
				'method' => 'PATCH',
				'headers' => $headers,
				'body' => json_encode( array(
						'sso' => $sso,
					) )
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
}
