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
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/js/bootstrap.min.js' );
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_URL . 'assets/css/initial-setup.css' );
		wp_enqueue_style( 'media' );
	}

	public function render_settings_page() {
		global $wpdb;
		$sql = 'SELECT *
				FROM ' . $wpdb->auth0_error_logs .'
				WHERE date > %s
				ORDER BY date DESC';

		$data = $wpdb->get_results( $wpdb->prepare( $sql, date( 'c', strtotime( '1 month ago' ) ) ) );

		if ( is_null( $data ) || $data instanceof WP_Error ) {
			return null;
		}

		include WPA0_PLUGIN_DIR . 'templates/a0-error-log.php';
	}

}
