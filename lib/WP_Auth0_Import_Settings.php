<?php

class WP_Auth0_Import_Settings {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render_import_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/import_settings.php';
	}

	public function import_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Unauthorized.', 'wp-auth0' ) );
			exit;
		}

		$settings_json = trim( stripslashes( $_POST['settings-json'] ?? '' ) );
		if ( empty( $settings_json ) ) {
			wp_safe_redirect( $this->make_error_url( __( 'No settings JSON entered.', 'wp-auth0' ) ) );
			exit;
		}

		$settings = json_decode( $settings_json, true );
		if ( empty( $settings ) || ! is_array( $settings ) ) {
			wp_safe_redirect( $this->make_error_url( __( 'Settings JSON entered is not valid.', 'wp-auth0' ) ) );
			exit;
		}

		// Keep original settings keys so we only save imported values.
		$settings_keys = array_keys( $settings );

		$admin = new WP_Auth0_Admin( $this->a0_options, new WP_Auth0_Routes( $this->a0_options ) );

		// Default setting values will be added to the array.
		$settings_validated = $admin->input_validator( $settings );

		foreach ( $settings_keys as $settings_key ) {
			// Invalid settings keys are removed in WP_Auth0_Admin::input_validator().
			if ( isset( $settings_validated[ $settings_key ] ) ) {
				$this->a0_options->set( $settings_key, $settings_validated[ $settings_key ], false );
			}
		}

		$this->a0_options->update_all();
		wp_safe_redirect( admin_url( 'admin.php?page=wpa0' ) );
		exit;
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function make_error_url( $error ) {
		return admin_url( 'admin.php?page=wpa0-import-settings&error=' . rawurlencode( $error ) );
	}
}
