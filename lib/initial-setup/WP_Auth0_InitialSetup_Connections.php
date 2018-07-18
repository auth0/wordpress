<?php

class WP_Auth0_InitialSetup_Connections {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/connections.php';
	}

	/**
	 * TODO: Deprecate, not used
	 */
	public function update_connection() {

		$provider_name = $_POST['connection'];

		if ( $provider_name == 'auth0' ) {
			$this->toggle_db();
		} else {
			$this->toggle_social( $provider_name );
		}
	}

	/**
	 * TODO: Remove when self::update_connection() is removed
	 */
	protected function toggle_db() {
		exit;
	}

	/**
	 * TODO: Remove when self::update_connection() is removed
	 */
	protected function toggle_social( $provider_name ) {
		exit;
	}

	public function callback() {
		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5' ) );
	}

	public function add_validation_error( $error ) {
		wp_redirect(
			admin_url(
				'admin.php?page=wpa0-setup&step=5&error=' .
				urlencode( 'There was an error setting up your connections.' )
			)
		);
		exit;
	}
}
