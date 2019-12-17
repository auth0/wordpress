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
		if ( empty( $settings ) ) {
			wp_safe_redirect( $this->make_error_url( __( 'Settings JSON entered is not valid.', 'wp-auth0' ) ) );
			exit;
		}

		foreach ( $settings as $key => $value ) {
			$this->a0_options->set( $key, $value, false );
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
