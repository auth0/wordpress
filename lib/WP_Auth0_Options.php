<?php

class WP_Auth0_Options {

	/**
	 * Name used in options table option_name column.
	 *
	 * @var string
	 */
	protected $_options_name = 'wp_auth0_settings';

	/**
	 * Current array of options stored in memory.
	 *
	 * @var null|array
	 */
	private $_opts = null;

	/**
	 * Array of options overridden by constants.
	 *
	 * @var array
	 */
	protected $constant_opts = [];

	/**
	 * @var WP_Auth0_Options
	 */
	protected static $_instance = null;

	/**
	 * WP_Auth0_Options constructor.
	 * Finds and stores all constant-defined settings values.
	 */
	public function __construct() {
		$option_keys = $this->get_defaults( true );
		foreach ( $option_keys as $key ) {
			$setting_const = $this->get_constant_name( $key );
			if ( defined( $setting_const ) ) {
				$this->constant_opts[ $key ] = constant( $setting_const );
			}
		}
	}

	/**
	 * @return WP_Auth0_Options
	 */
	public static function Instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Takes an option key and creates the constant name to look for.
	 *
	 * @param string $key - Option key to transform.
	 *
	 * @return string
	 */
	public function get_constant_name( $key ) {
		// NOTE: the add_filter call must load before WP_Auth0::init() so it cannot be used in a theme.
		$constant_prefix = apply_filters( 'auth0_settings_constant_prefix', 'AUTH0_ENV_' );
		return $constant_prefix . strtoupper( $key );
	}

	/**
	 * Does a certain option pull from a constant?
	 *
	 * @param string $key - Option key to check.
	 *
	 * @return boolean
	 */
	public function has_constant_val( $key ) {
		return isset( $this->constant_opts[ $key ] );
	}

	/**
	 * Get the value of an overriding constant if one is set, return null if not.
	 *
	 * @param string $key - Option key to look for.
	 *
	 * @return string|null
	 */
	public function get_constant_val( $key ) {
		return $this->has_constant_val( $key ) ? constant( $this->get_constant_name( $key ) ) : null;
	}

	/**
	 * Get all the keys for constant-overridden settings.
	 *
	 * @return array
	 */
	public function get_all_constant_keys() {
		return array_keys( $this->constant_opts );
	}

	/**
	 * Get the option_name for the settings array.
	 *
	 * @return string
	 */
	public function get_options_name() {
		return $this->_options_name;
	}

	/**
	 * Return options from memory, database, defaults, or constants.
	 *
	 * @return array
	 */
	public function get_options() {
		if ( empty( $this->_opts ) ) {
			$options = get_option( $this->_options_name, [] );
			// Brand new install, no saved options so get all defaults.
			if ( empty( $options ) || ! is_array( $options ) ) {
				$options = $this->defaults();
			}

			// Check for constant overrides and replace.
			if ( ! empty( $this->constant_opts ) ) {
				$options = array_replace_recursive( $options, $this->constant_opts );
			}
			$this->_opts = $options;
		}
		return $this->_opts;
	}

	/**
	 * Return a filtered settings value or default.
	 *
	 * @param string $key - Settings key to get.
	 * @param mixed  $default - Default value to return if not found.
	 *
	 * @return mixed
	 *
	 * @link https://auth0.com/docs/cms/wordpress/extending#wp_auth0_get_option
	 */
	public function get( $key, $default = null ) {
		$options = $this->get_options();
		$value   = isset( $options[ $key ] ) ? $options[ $key ] : $default;
		return apply_filters( 'wp_auth0_get_option', $value, $key );
	}

	/**
	 * Update a setting if not already stored in a constant.
	 * This method will fail silently if the option is already set in a constant.
	 *
	 * @param string $key - Option key name to update.
	 * @param mixed  $value - Value to update with.
	 * @param bool   $should_update - Flag to update DB options array with value stored in memory.
	 *
	 * @return bool
	 */
	public function set( $key, $value, $should_update = true ) {

		// Cannot set a setting that is being overridden by a constant.
		if ( $this->has_constant_val( $key ) ) {
			return false;
		}

		$options         = $this->get_options();
		$options[ $key ] = $value;
		$this->_opts     = $options;

		// No database update so process completed successfully.
		if ( ! $should_update ) {
			return true;
		}

		return $this->update_all();
	}

	/**
	 * Remove a setting from the options array in memory.
	 *
	 * @param string $key - Option key name to remove.
	 */
	public function remove( $key ) {

		// Cannot remove a setting that is being overridden by a constant.
		if ( $this->has_constant_val( $key ) ) {
			return;
		}

		$options = $this->get_options();
		unset( $options[ $key ] );
		$this->_opts = $options;
	}

	/**
	 * Save the options array as it exists in memory.
	 *
	 * @return bool
	 */
	public function update_all() {
		$options = $this->get_options();

		foreach ( $this->get_all_constant_keys() as $key ) {
			unset( $options[ $key ] );
		}
		return update_option( $this->_options_name, $options );
	}

	/**
	 * Save the options array for the first time.
	 */
	public function save() {
		$this->get_options();
		$this->update_all();
	}

	/**
	 * Delete the options array.
	 *
	 * @return bool
	 */
	public function delete() {
		return delete_option( $this->_options_name );
	}

	/**
	 * Reset options to defaults.
	 */
	public function reset() {
		$this->_opts = null;
		$this->delete();
		$this->save();
	}

	/**
	 * Return default options as key => value or just keys.
	 *
	 * @param bool $keys_only - Only return the array keys for the default options.
	 *
	 * @return array
	 */
	public function get_defaults( $keys_only = false ) {
		$default_opts = $this->defaults();
		return $keys_only ? array_keys( $default_opts ) : $default_opts;
	}

	public function is_wp_registration_enabled() {
		return is_multisite() ? users_can_register_signup_filter() : get_site_option( 'users_can_register' );
	}

	public function get_default( $key ) {
		$defaults = $this->defaults();
		return $defaults[ $key ];
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
			? [ $home_url_origin ]
			: [ $home_url_origin, $site_url_origin ];
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
		return add_query_arg( 'auth0', 1, $site_url );
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
		$connections = empty( $connections ) ? [] : explode( ',', $connections );
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
	 * Default settings when plugin is installed or reset
	 *
	 * @return array
	 */
	protected function defaults() {
		return [

			// System
			'version'                   => 1,
			'last_step'                 => 1,
			'migration_token_id'        => null,

			// Basic
			'domain'                    => '',
			'custom_domain'             => '',
			'client_id'                 => '',
			'client_secret'             => '',
			'client_signing_algorithm'  => WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG,
			'cache_expiration'          => 1440,
			'wordpress_login_enabled'   => 'link',
			'wle_code'                  => '',

			// Features
			'auto_login'                => 1,
			'auto_login_method'         => '',
			'singlelogout'              => 1,
			'override_wp_avatars'       => 1,

			// Embedded
			'passwordless_enabled'      => false,
			'icon_url'                  => '',
			'form_title'                => '',
			'gravatar'                  => true,
			'username_style'            => '',
			'primary_color'             => '',
			'extra_conf'                => '',
			'custom_cdn_url'            => null,
			'cdn_url'                   => WPA0_LOCK_CDN_URL,
			'lock_connections'          => '',

			// Advanced
			'requires_verified_email'   => true,
			'skip_strategies'           => '',
			'remember_users_session'    => false,
			'default_login_redirection' => home_url(),
			'force_https_callback'      => false,
			'auto_provisioning'         => false,
			'migration_ws'              => false,
			'migration_token'           => '',
			'migration_ips_filter'      => false,
			'migration_ips'             => '',
			'valid_proxy_ip'            => '',
			'auth0_server_domain'       => 'auth0.auth0.com',
		];
	}
}
