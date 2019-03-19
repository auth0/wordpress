<?php
/**
 * Contains WP_Auth0_Options_Generic.
 *
 * @package WP-Auth0
 */

/**
 * Class WP_Auth0_Options_Generic.
 */
class WP_Auth0_Options_Generic {

	/**
	 * Name used in options table option_name column.
	 *
	 * @var string
	 */
	protected $_options_name = '';

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
	protected $constant_opts = array();

	/**
	 * WP_Auth0_Options_Generic constructor.
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
			$options = get_option( $this->_options_name, array() );
			if ( empty( $options ) || ! is_array( $options ) ) {

				// Brand new install, no saved options so get all defaults.
				$options = $this->defaults();
			} else {

				// Make sure we have settings for everything we need.
				$options = array_merge( $this->defaults(), $options );
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
		$options = $this->get_options();

		// Cannot set a setting that is being overridden by a constant.
		if ( $this->has_constant_val( $key ) ) {
			return false;
		}

		$options[ $key ] = $value;
		$this->_opts     = $options;

		// No database update so process completed successfully.
		if ( ! $should_update ) {
			return true;
		}

		return $this->update_all();
	}

	/**
	 * Save the options array as it exists in memory.
	 *
	 * @return bool
	 */
	public function update_all() {
		foreach ( $this->get_all_constant_keys() as $key ) {
			unset( $this->_opts[ $key ] );
		}
		return update_option( $this->_options_name, $this->_opts );
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

	/**
	 * Default settings when plugin is installed or reset
	 *
	 * @return array
	 */
	protected function defaults() {
		return array();
	}
}
