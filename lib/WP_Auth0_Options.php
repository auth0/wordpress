<?php

class WP_Auth0_Options extends WP_Auth0_Options_Generic {

	protected static $instance = null;
	public static function Instance() {
		if ( self::$instance === null ) {
			self::$instance = new WP_Auth0_Options;
		}
		return self::$instance;
	}

	protected $options_name = 'wp_auth0_settings';

	public function is_wp_registration_enabled() {
		if ( is_multisite() ) {
			return users_can_register_signup_filter();
		}
		return get_site_option( 'users_can_register', 0 ) == 1;
	}

	public function get_enabled_connections() {
		return array( 'facebook', 'twitter', 'google-oauth2' );
	}

	public function set_connection( $key, $value ) {
		$options = $this->get_options();
		$options['connections'][$key] = $value;

		$this->set( 'connections', $options['connections'] );
	}

	public function get_connection( $key, $default = null ) {
		$options = $this->get_options();

		if ( !isset( $options['connections'][$key] ) )
			return apply_filters( 'wp_auth0_get_option', $default, $key );
		return apply_filters( 'wp_auth0_get_option', $options['connections'][$key], $key );
	}

	public function get_default($key) {
		$defaults = $this->defaults();
		return $defaults[$key];
	}

	public function get_client_signing_algorithm() {
			$client_signing_algorithm = $this->get('client_signing_algorithm', WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG);
			return $client_signing_algorithm;
	}

	/**
	 * Get the currently-stored client ID as a JWT key
	 *
	 * @param bool $legacy - legacy installs did not provide RS256, forces HS256
	 *
	 * @return bool|string
	 */
	public function get_client_secret_as_key( $legacy = false ) {
		return $this->convert_client_secret_to_key(
			$this->get('client_secret', ''),
			$this->get('client_secret_b64_encoded', false),
			( $legacy ? false : $this->get_client_signing_algorithm() === 'RS256' ),
			$this->get( 'domain' )
		);
	}

	/**
	 * Convert a client_secret value into a JWT key
	 *
	 * @param string $secret - client_secret value
	 * @param bool $is_encoded - is the client_secret base64 encoded?
	 * @param bool $is_RS256 - if true, use RS256; if false, use HS256
	 * @param string $domain - tenant domain
	 *
	 * @return array|bool|mixed|string
	 */
	public function convert_client_secret_to_key( $secret, $is_encoded, $is_RS256, $domain ) {
		if ( $is_RS256 ) {
			return WP_Auth0_Api_Client::JWKfetch( $domain );
		} else {
			return $is_encoded ? JWT::urlsafeB64Decode( $secret ) : $secret;
		}
	}

	/**
	 * Get web_origin settings for new Clients
	 *
	 * @return array
	 */
	public function get_web_origins() {
		$home_url_parsed = parse_url( home_url() );
		$home_url_origin = str_replace( $home_url_parsed[ 'path' ], '', home_url() );

		$site_url_parsed = parse_url( site_url() );
		$site_url_origin = str_replace( $site_url_parsed[ 'path' ], '', site_url() );

		return $home_url_origin === $site_url_origin
			? array( $home_url_origin )
			: array( $home_url_origin, $site_url_origin );
	}

	/**
	 * Get the main site URL for Auth0 processing
	 *
	 * @param string|null $protocol - forced URL protocol, use default if empty
	 *
	 * @return string
	 */
	public function get_wp_auth0_url( $protocol = null ) {
		$site_url = site_url( 'index.php', $protocol );
		return add_query_arg( 'auth0', '1', $site_url );
	}

	/**
	 * Get get_cross_origin_loc URL for new Clients
	 *
	 * @return string
	 */
	public function get_cross_origin_loc() {
		return add_query_arg( 'auth0fallback', '1', site_url( 'index.php', 'https' ) );
	}

	/**
	 * Get the main site logout URL, minus a nonce
	 *
	 * @return string
	 */
	public function get_logout_url() {
		return add_query_arg( 'action', 'logout', site_url( 'wp-login.php', 'login' ) );
	}

	/**
	 * Default settings when plugin is installed or reset
	 *
	 * @return array
	 */
	protected function defaults() {
		return array(

			// System
			'version' => 1,
			'last_step' => 1,
			'migration_token_id' => null,
			'use_lock_10' => true,
			'jwt_auth_integration' => false,
			'amplificator_title' => '',
			'amplificator_subtitle' => '',
			'connections' => array(),
			'auth0js-cdn' => '//cdn.auth0.com/js/auth0/9.1/auth0.min.js',

			// Basic
			'domain' => '',
			'client_id' => '',
			'client_secret' => '',
			'client_secret_b64_encoded' => null,
			'client_signing_algorithm' => WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG,
			'cache_expiration' => 1440,
			'auth0_app_token' => null,
			'api_audience' => null,
			'wordpress_login_enabled' => true,

			// Features
			'password_policy' => 'fair',
			'sso' => false,
			'singlelogout' => false,
			'mfa' => null,
			'fullcontact' => null,
			'fullcontact_rule' => null,
			'fullcontact_apikey' => null,
			'geo_rule' => null,
			'income_rule' => null,
			'override_wp_avatars' => true,

			// Appearance
			'form_title' => '',
			'social_big_buttons' => false,
			'icon_url' => '',
			'gravatar' => true,
			'custom_css' => '',
			'custom_js' => '',
			'username_style' => '',
			'primary_color' => '',
			'language' => '',
			'language_dictionary' => '',

			// Advanced
			'auto_provisioning' => false,
			'passwordless_enabled' => false,
			'passwordless_method' => 'magiclink',
			'force_https_callback' => false,
			'cdn_url' => '//cdn.auth0.com/js/lock/11.1/lock.min.js',
			'cdn_url_legacy' => '//cdn.auth0.com/js/lock-9.2.min.js',
			'passwordless_cdn_url' => '//cdn.auth0.com/js/lock-passwordless-2.2.min.js',
			'lock_connections' => '',
			'remember_users_session' => false,
			'link_auth0_users' => null,
			'social_twitter_key' => '',
			'social_twitter_secret' => '',
			'social_facebook_key' => '',
			'social_facebook_secret' => '',
			'migration_ws' => false,
			'migration_token' => null,
			'migration_ips_filter' => false,
			'migration_ips' => null,
			'auth0_implicit_workflow' => false,
			'default_login_redirection' => home_url(),
			'requires_verified_email' => true,
			'auto_login' => 0,
			'auto_login_method' => '',
			'ip_range_check' => 0,
			'ip_ranges' => '',
			'valid_proxy_ip' => null,
			'custom_signup_fields' => '',
			'extra_conf' => '',
			'auth0_server_domain' => 'auth0.auth0.com',
			'metrics' => 1,
		);
	}
}
