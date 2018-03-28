<?php
class WP_Auth0_ErrorLog {

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
	}

	public function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-errors' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_BS_URL . 'css/bootstrap.min.css', FALSE, '3.3.5' );
		wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_BS_URL . 'js/bootstrap.min.js', array( 'jquery' ), '3.3.6' );
		wp_enqueue_style( 'media' );
	}

	public function render_settings_page() {

		$data = get_option('auth0_error_log', array());

		include WPA0_PLUGIN_DIR . 'templates/a0-error-log.php';
	}

}
