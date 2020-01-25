<?php

class WP_Auth0_Admin {

	protected $a0_options;

	protected $router;

	protected $sections = [];

	public function __construct( WP_Auth0_Options $a0_options, WP_Auth0_Routes $router ) {
		$this->a0_options = $a0_options;
		$this->router     = $router;
	}

	/**
	 * Enqueue scripts for all Auth0 wp-admin pages
	 */
	public function admin_enqueue() {
		// Register admin styles
		wp_register_style( 'wpa0_admin_initial_setup', WPA0_PLUGIN_CSS_URL . 'initial-setup.css', false, WPA0_VERSION );

		// Register admin scripts
		wp_register_script( 'wpa0_async', WPA0_PLUGIN_LIB_URL . 'async.min.js', false, WPA0_VERSION );
		wp_register_script( 'wpa0_admin', WPA0_PLUGIN_JS_URL . 'admin.js', [ 'jquery' ], WPA0_VERSION );
		wp_localize_script(
			'wpa0_admin',
			'wpa0',
			[
				'media_title'             => __( 'Choose your icon', 'wp-auth0' ),
				'media_button'            => __( 'Choose icon', 'wp-auth0' ),
				'ajax_working'            => __( 'Working ...', 'wp-auth0' ),
				'ajax_done'               => __( 'Done!', 'wp-auth0' ),
				'refresh_prompt'          => __( 'Save or refresh this page to see changes.', 'wp-auth0' ),
				'clear_cache_nonce'       => wp_create_nonce( 'auth0_delete_cache_transient' ),
				'rotate_token_nonce'      => wp_create_nonce( WP_Auth0_Admin_Advanced::ROTATE_TOKEN_NONCE_ACTION ),
				'form_confirm_submit_msg' => __( 'Are you sure?', 'wp-auth0' ),
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
			]
		);

		$wpa0_pages     = [ 'wpa0', 'wpa0-errors', 'wpa0-import-settings', 'wpa0-setup' ];
		$wpa0_curr_page = ! empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if ( ! in_array( $wpa0_curr_page, $wpa0_pages ) ) {
			return false;
		}

		wp_enqueue_script( 'wpa0_admin' );
		wp_enqueue_script( 'wpa0_async' );

		if ( 'wpa0' === $wpa0_curr_page ) {
			wp_enqueue_media();
			wp_enqueue_style( 'media' );
		}

		wp_enqueue_style( 'wpa0_admin_initial_setup' );
		return true;
	}

	public function init_admin() {
		$this->sections['basic'] = new WP_Auth0_Admin_Basic( $this->a0_options );
		$this->sections['basic']->init();

		$this->sections['features'] = new WP_Auth0_Admin_Features( $this->a0_options );
		$this->sections['features']->init();

		$this->sections['appearance'] = new WP_Auth0_Admin_Appearance( $this->a0_options );
		$this->sections['appearance']->init();

		$this->sections['advanced'] = new WP_Auth0_Admin_Advanced( $this->a0_options, $this->router );
		$this->sections['advanced']->init();

		register_setting(
			$this->a0_options->get_options_name() . '_basic',
			$this->a0_options->get_options_name(),
			[ $this, 'input_validator' ]
		);
	}

	/**
	 * Main validator for settings page inputs.
	 * Delegates validation to settings sections in self::init_admin().
	 *
	 * @param array $input - Incoming array of settings fields to validate.
	 *
	 * @return mixed
	 */
	public function input_validator( array $input ) {
		$constant_keys = $this->a0_options->get_all_constant_keys();

		// Look for and set constant overrides so validation is still possible.
		foreach ( $constant_keys as $key ) {
			$input[ $key ] = $this->a0_options->get_constant_val( $key );
		}

		foreach ( $this->sections as $name => $section ) {
			$input = $section->input_validator( $input );
		}

		// Remove constant overrides so they are not saved to the database.
		foreach ( $constant_keys as $key ) {
			unset( $input[ $key ] );
		}

		return $input;
	}

	public function render_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/settings.php';
	}
}
