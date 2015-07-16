<?php

class WP_Auth0_Api_Client {

	public static function get_info_headers() {
		global $wp_version;

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

	public static function get_token($domain, $client_id, $client_secret, $grantType = 'client_credentials', $extraBody = null) {
		if ( ! is_array( $extraBody ) ) {
			$body = array();
		} else {
			$body = $extraBody;
		}

		$endpoint = "https://$domain/";

		$body['client_id'] = $client_id;
		$body['client_secret'] = $client_secret;
		$body['grant_type'] = $grantType;

		$headers = self::get_info_headers();
		$headers['content-type'] = 'application/x-www-form-urlencoded';


		$response = wp_remote_post( $endpoint . 'oauth/token', array(
			'headers' => $headers,
			'body' => $body,
		) );

		if ( $response instanceof WP_Error ) {
			WP_Auth0::insert_auth0_error( 'WP_Auth0_Api_Client::get_token', $response );
			error_log( $response->get_error_message() );
		}

		return $response;
	}

	public static function get_user_info($domain, $access_token) {

		$endpoint = "https://$domain/";

		$headers = self::get_info_headers();

		return wp_remote_get( $endpoint . 'userinfo/?access_token=' . $access_token , array(
			'headers' => $headers,
		) );

	}

	public static function get_user($domain, $jwt, $user_id) {
		$endpoint = "https://$domain/api/v2/users/" . urlencode( $user_id );

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $jwt";

		return wp_remote_get( $endpoint  , array(
			'headers' => $headers,
		) );
	}

	public static function create_client($domain, $app_token, $name, $callbackUrl) {

		$endpoint = "https://$domain/api/v2/clients";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
			'method' => 'POST',
			'headers' => $headers,
			'body' => json_encode(array(
				'name' => $name,
				'callbacks' => array( $callbackUrl )
			))
		) );

		if ( $response['response']['code'] !== 201 ) return false;

		return json_decode($response['body']);
	}

	public static function update_client($domain, $app_token, $client_id, $sso) {

		$endpoint = "https://$domain/api/v2/clients/$client_id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
			'method' => 'PATCH',
			'headers' => $headers,
			'body' => json_encode(array(
				'sso' => $sso,
			))
		) );

		if ( $response['response']['code'] !== 201 ) return false;

		return json_decode($response['body']);
	}

	public static function create_rule($domain, $app_token, $name, $script, $enabled = true) {
		$payload = array(
			"name" => $name,
			"script" => $script,
			// "order" => 2,
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
			'body' => json_encode($payload)
		) );

		if ( $response['response']['code'] >= 300 ) return false;

		return json_decode($response['body']);
	}

	public static function delete_rule($domain, $app_token, $id) {

		$endpoint = "https://$domain/api/v2/rules/$id";

		$headers = self::get_info_headers();

		$headers['Authorization'] = "Bearer $app_token";
		$headers['content-type'] = "application/json";

		$response = wp_remote_post( $endpoint  , array(
			'method' => 'DELETE',
			'headers' => $headers
		) );

		if ( $response['response']['code'] !== 200 ) return false;

		return json_decode($response['body']);
	}
}
