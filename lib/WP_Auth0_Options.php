<?php

class WP_Auth0_Options extends WP_Auth0_Options_Generic {

	protected static $_instance = null;
	protected $_options_name    = 'wp_auth0_settings';

	public static function Instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new WP_Auth0_Options;
		}
		return self::$_instance;
	}

	public function is_wp_registration_enabled() {
		return is_multisite() ? users_can_register_signup_filter() : get_site_option( 'users_can_register' );
	}

	public function get_default( $key ) {
		$defaults = $this->defaults();
		return $defaults[ $key ];
	}

	/**
	 * Get the stored token signing algorithm
	 *
	 * @return string
	 */
	public function get_client_signing_algorithm() {
		return $this->get( 'client_signing_algorithm', WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG );
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
			$this->get( 'client_secret', '' ),
			$this->get( 'client_secret_b64_encoded', false ),
			( $legacy ? false : $this->get_client_signing_algorithm() === 'RS256' ),
			$this->get_auth_domain()
		);
	}

	/**
	 * Convert a client_secret value into a JWT key
	 *
	 * @param string $secret - client_secret value
	 * @param bool   $is_encoded - is the client_secret base64 encoded?
	 * @param bool   $is_RS256 - if true, use RS256; if false, use HS256
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
		$home_url_parsed = wp_parse_url( home_url() );
		$home_url_origin = ! empty( $home_url_parsed['path'] )
			? str_replace( $home_url_parsed['path'], '', home_url() )
			: home_url();

		$site_url_parsed = wp_parse_url( site_url() );
		$site_url_origin = ! empty( $site_url_parsed['path'] )
			? str_replace( $site_url_parsed['path'], '', site_url() )
			: site_url();

		return $home_url_origin === $site_url_origin
			? array( $home_url_origin )
			: array( $home_url_origin, $site_url_origin );
	}

	/**
	 * Get the main site URL for Auth0 processing
	 *
	 * @param string|null $protocol - forced URL protocol, use default if empty
	 * @param bool        $implicit - use the implicit flow in the callback
	 *
	 * @return string
	 */
	public function get_wp_auth0_url( $protocol = null, $implicit = false ) {
		$site_url = site_url( 'index.php', $protocol );
		return add_query_arg( 'auth0', ( $implicit ? 'implicit' : '1' ), $site_url );
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
	 * Get a custom Lock URL or the default, depending on settings.
	 *
	 * @return string
	 */
	public function get_lock_url() {
		$cdn_url = $this->get( 'cdn_url' );
		return $cdn_url && $this->get( 'custom_cdn_url' ) ? $cdn_url : WPA0_LOCK_CDN_URL;
	}

	/**
	 * Get the authentication domain.
	 *
	 * @return string
	 */
	public function get_auth_domain() {
		$domain = $this->get( 'custom_domain' );
		if ( empty( $domain ) ) {
			$domain = $this->get( 'domain' );
		}
		return $domain;
	}

	/**
	 * Get lock_connections as an array of strings
	 *
	 * @return array
	 */
	public function get_lock_connections() {
		$connections = $this->get( 'lock_connections' );
		$connections = empty( $connections ) ? array() : explode( ',', $connections );
		return array_map( 'trim', $connections );
	}

	/**
	 * Add a new connection to the lock_connections setting
	 *
	 * @param string $connection - connection name to add
	 */
	public function add_lock_connection( $connection ) {
		$connections = $this->get_lock_connections();

		// Add if it doesn't exist already
		if ( ! array_key_exists( $connection, $connections ) ) {
			$connections[] = $connection;
			$connections   = implode( ',', $connections );
			$this->set( 'lock_connections', $connections );
		}
	}

	/**
	 * Check if provided strategy is allowed to skip email verification.
	 * Useful for Enterprise strategies that do not provide a email_verified profile value.
	 *
	 * @param string $strategy - Strategy to check against saved setting.
	 *
	 * @return bool
	 *
	 * @since 3.8.0
	 */
	public function strategy_skips_verified_email( $strategy ) {
		$skip_strategies = trim( $this->get( 'skip_strategies' ) );

		// No strategies to skip.
		if ( empty( $skip_strategies ) ) {
			return false;
		}

		$skip_strategies = explode( ',', $skip_strategies );
		$skip_strategies = array_map( 'trim', $skip_strategies );
		return in_array( $strategy, $skip_strategies );
	}

	/**
	 * @return bool
	 */
	public function can_show_wp_login_form() {

		if ( ! isset( $_GET['wle'] ) ) {
			return false;
		}

		$wle_setting = $this->get( 'wordpress_login_enabled' );
		if ( 'no' === $wle_setting ) {
			return false;
		}

		if ( in_array( $wle_setting, array( 'link', 'isset' ) ) ) {
			return true;
		}

		$wle_code = $this->get( 'wle_code' );
		if ( 'code' === $wle_setting && $wle_code === $_GET['wle'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Default settings when plugin is installed or reset
	 *
	 * @return array
	 */
	protected function defaults() {
		return array(

			// System
			'version'                   => 1,
			'last_step'                 => 1,
			'migration_token_id'        => null,
			'jwt_auth_integration'      => false,

			// Basic
			'domain'                    => '',
			'custom_domain'             => '',
			'client_id'                 => '',
			'client_secret'             => '',
			'client_secret_b64_encoded' => null,
			'client_signing_algorithm'  => WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG,
			'cache_expiration'          => 1440,
			'wordpress_login_enabled'   => 'link',
			'wle_code'                  => '',

			// Features
			'sso'                       => 0,
			'singlelogout'              => 1,
			'override_wp_avatars'       => 1,

			// Appearance
			'icon_url'                  => '',
			'form_title'                => '',
			'gravatar'                  => true,
			'username_style'            => '',
			'primary_color'             => '',
			'language'                  => '',
			'language_dictionary'       => '',

			// Advanced
			'requires_verified_email'   => true,
			'skip_strategies'           => '',
			'remember_users_session'    => false,
			'default_login_redirection' => home_url(),
			'passwordless_enabled'      => false,
			'force_https_callback'      => false,
			'cdn_url'                   => WPA0_LOCK_CDN_URL,
			'custom_cdn_url'            => null,
			'lock_connections'          => '',
			'auto_provisioning'         => false,
			'migration_ws'              => false,
			'migration_token'           => null,
			'migration_ips_filter'      => false,
			'migration_ips'             => null,
			'auto_login'                => 1,
			'auto_login_method'         => '',
			'auth0_implicit_workflow'   => false,
			'valid_proxy_ip'            => null,
			'custom_signup_fields'      => '',
			'extra_conf'                => '',
			'auth0_server_domain'       => 'auth0.auth0.com',
		);
	}

	/*
	 *
	 * DEPRECATED
	 *
	 */

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided. Connection settings now live in top-level settings.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function set_connection( $key, $value ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$options                        = $this->get_options();
		$options['connections'][ $key ] = $value;

		$this->set( 'connections', $options['connections'] );
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided. Connection settings now live in top-level settings.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_connection( $key, $default = null ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$options = $this->get_options();

		if ( ! isset( $options['connections'][ $key ] ) ) {
			return apply_filters( 'wp_auth0_get_option', $default, $key );
		}
		return apply_filters( 'wp_auth0_get_option', $options['connections'][ $key ], $key );
	}

	/**
	 * @deprecated - 3.6.0, social connections are no longer set during initial setup so this data is no longer needed.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_enabled_connections() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return array( 'facebook', 'twitter', 'google-oauth2' );
	}
}
