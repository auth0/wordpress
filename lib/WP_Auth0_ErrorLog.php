<?php
class WP_Auth0_ErrorLog {

	/**
	 *
	 * @deprecated 3.6.0 - Not needed, handled in WP_Auth0_Admin::admin_enqueue()
	 */
	public function init() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}

	/**
	 *
	 * @deprecated 3.6.0 - Not needed, handled in WP_Auth0_Admin::admin_enqueue()
	 */
	public function admin_enqueue() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}

	public function render_settings_page() {

		$data = get_option( 'auth0_error_log', array() );

		include WPA0_PLUGIN_DIR . 'templates/a0-error-log.php';
	}

}
