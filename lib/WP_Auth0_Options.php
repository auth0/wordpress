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

	public function get_client_secret_as_key($legacy = false) {
		$secret = $this->get('client_secret', '');

		$isEncoded = $this->get('client_secret_b64_encoded', false);
		$isRS256 = $legacy ? false : $this->get_client_signing_algorithm() === 'RS256';

		if ( $isRS256 ) {
			$domain = $this->get( 'domain' );
			$secret = WP_Auth0_Api_Client::JWKfetch($domain);
		} else {
			$secret = $isEncoded ? JWT::urlsafeB64Decode($secret) : $secret;
		}
  	
		return $secret;
	}

	public function get_client_signing_algorithm() {
			$client_signing_algorithm = $this->get('client_signing_algorithm', 'RS256');
			return $client_signing_algorithm;
	}
	
	protected function defaults() {
		return array(
			'version' => 1,
			'metrics' => 1,
			'last_step' => 1,
			'auto_login' => 0,
			'auto_login_method' => '',
			'client_id' => '',
			'client_secret' => '',
			'client_signing_algorithm' => 'RS256',
			'cache_expiration' => 1440,
			'client_secret_b64_encoded' => null,
			'domain' => '',
			'form_title' => '',
			'icon_url' => '',
			'ip_range_check' => 0,
			'ip_ranges' => '',
			'lock_connections' => '',
			'passwordless_enabled' => false,
			'passwordless_method' => 'magiclink',
			'passwordless_cdn_url' => '//cdn.auth0.com/js/lock-passwordless-2.2.min.js',
			'use_lock_10' => true,
			'cdn_url' => '//cdn.auth0.com/js/lock/11.0.0/lock.min.js',
			'cdn_url_legacy' => '//cdn.auth0.com/js/lock-9.2.min.js',
			'requires_verified_email' => true,
			'wordpress_login_enabled' => true,
			'primary_color' => '',

			'language' => '',
			'language_dictionary' => '',

			'custom_signup_fields' => '',

			'social_big_buttons' => false,
			'username_style' => '',
			'extra_conf' => '',
			'custom_css' => '',
			'custom_js' => '',
			'auth0_implicit_workflow' => false,
			'sso' => false,
			'singlelogout' => false,
			'gravatar' => true,
			'jwt_auth_integration' => false,
			'auth0_app_token' => null,
			'mfa' => null,
			'fullcontact' => null,
			'fullcontact_rule' => null,
			'fullcontact_apikey' => null,
			'geo_rule' => null,
			'income_rule' => null,
			'link_auth0_users' => null,
			'remember_users_session' => false,

			'override_wp_avatars' => true,

			'migration_ws' => false,
			'migration_token' => null,
			'migration_token_id' => null,
			'migration_ips_filter' => false,
			'migration_ips' => null,
			'valid_proxy_ip' => null,

			'amplificator_title' => '',
			'amplificator_subtitle' => '',

			'connections' => array(),

			'password_policy' => 'fair',

			'force_https_callback' => false,

			'auto_provisioning' => false,
			'default_login_redirection' => home_url(),

      		'auth0_server_domain' => 'auth0.auth0.com',
			'auth0js-cdn' => '//cdn.auth0.com/js/auth0/9.0.0/auth0.min.js',

			//DASHBOARD
			'chart_idp_type' => 'donut',
			'chart_gender_type' => 'donut',
			'chart_age_type' => 'donut',

			'chart_age_from' => '10',
			'chart_age_to' => '70',
			'chart_age_step' => '5',
		);
	}
}
