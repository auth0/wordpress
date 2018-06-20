<?php

class WP_Auth0_Options_Generic {
	protected $_options_name = '';
	private $_opt = null;
	protected $constant_opts = array();

	/**
	 * WP_Auth0_Options_Generic constructor.
	 * Finds and stores all constant-defined settings values.
	 */
	public function __construct() {
		$option_keys = array_keys( $this->defaults() );
		foreach ( $option_keys as $key ) {
			$const_name = $this->get_constant_name( $key );
			if ( defined( $const_name ) ) {
				$this->constant_opts[$key] = constant( $const_name );
			}
		}
	}

	/**
	 * Get the option table name for the settings being used.
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
		if ( empty( $this->_opt ) ) {
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
				$options = array_merge( $options, $this->constant_opts );
			}

			$this->_opt = $options;
		}
		return $this->_opt;
	}

	/**
	 * Return a filtered settings value or default.
	 *
	 * @param string $key - Settings key to get.
	 * @param mixed $default - Default value to return if not found.
	 *
	 * @return mixed
	 *
	 * @link https://auth0.com/docs/cms/wordpress/extending#wp_auth0_get_option
	 */
	public function get( $key, $default = null ) {
		$options = $this->get_options();
		$value = isset( $options[$key] ) ? $options[$key] : $default;
		return apply_filters( 'wp_auth0_get_option', $value, $key );
	}

	/**
	 * Return a filtered connection settings value or default.
	 *
	 * @param string $key - Connection option key to look for.
	 * @param mixed $default - Default value to return if not found.
	 *
	 * @return mixed
	 *
	 * @link https://auth0.com/docs/cms/wordpress/extending#wp_auth0_get_option
	 */
	public function get_connection( $key, $default = null ) {
		$options = $this->get_options();
		$value = isset( $options['connections'][$key] ) ? $options['connections'][$key] : $default;
		return apply_filters( 'wp_auth0_get_option', $value, $key );
	}

	/**
	 * Takes an option key and creates the constant name to look for.
	 *
	 * @param string $key - Option key to transform.
	 *
	 * @return string
	 */
	public function get_constant_name( $key ) {
		return 'AUTH0_ENV_' . strtoupper( $key );
	}

	/**
	 * Get the value of an overriding constant if one is set, return null if not.
	 *
	 * @param string $key - Option key to look for.
	 *
	 * @return string|null
	 */
	public function get_constant_val( $key ) {
		return isset( $this->constant_opts[$key] ) ? $this->constant_opts[$key] : null;
	}

	/**
	 * Get all the keys for constant-overridden settings.
	 *
	 * @return array
	 */
	public function get_all_constant_keys() {
		return ! empty( $this->constant_opts ) ? array_keys( $this->constant_opts ) : array();
	}

	/**
	 * Update a setting if not already stored in a constant.
	 * This method will fail silently if the option is already set in a constant.
	 *
	 * @param string $key - Option key name to update.
	 * @param mixed $value - Value to update with.
	 *
	 * @param bool $should_update
	 */
	public function set( $key, $value, $should_update = true ) {
		$options = $this->get_options();

		if ( null === $this->get_constant_val( $key ) ) {
			$options[$key] = $value;
			$this->_opt = $options;

			if ( $should_update ) {
				$this->update_all();
			}
		}
	}

	public function update_all() {
		update_option( $this->_options_name, $this->_opt );
	}

	public function save() {
		$this->get_options();
		$this->update_all();
	}

	public function delete() {
		delete_option( $this->_options_name );
	}

	protected function defaults() {
		return array();
	}
}