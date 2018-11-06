<?php
/**
 * Contains the WP_Auth0_ErrorLog class.
 *
 * @package WP-Auth0
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_ErrorLog.
 * Handles error log CRUD actions and hooks.
 */
class WP_Auth0_ErrorLog {

	/**
	 * Option name used to store the error log.
	 */
	const OPTION_NAME = 'auth0_error_log';

	/**
	 * Limit of the error logs that can be stored
	 */
	const ERROR_LOG_ENTRY_LIMIT = 30;

	/**
	 * Add actions and filters for the error log settings section.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/admin_action__requestaction/
	 */
	public function init() {
		add_action( 'admin_action_wpauth0_clear_error_log', 'wp_auth0_errorlog_clear_error_log' );
	}

	/**
	 * Render the settings page.
	 *
	 * @see WP_Auth0_Settings_Section::init_menu()
	 */
	public function render_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/a0-error-log.php';
	}

	/**
	 * Get the error log.
	 *
	 * @return array
	 */
	public function get() {
		$log = get_option( self::OPTION_NAME );

		if ( empty( $log ) ) {
			$log = array();
		}

		return $log;
	}

	/**
	 * Add a new log entry, checking for previous duplicates and limit.
	 *
	 * @param array $new_entry - New log entry to add.
	 *
	 * @return bool
	 */
	public function add( array $new_entry ) {
		$log = $this->get();

		// Prepare the last error log entry to compare with the new one.
		$last_entry = null;
		if ( ! empty( $log ) ) {
			// Get the last error logged.
			$last_entry = $log[0];

			// Remove date and count fields so it can be compared with the new error.
			$last_entry = array_diff_key( $last_entry, array_flip( array( 'date', 'count' ) ) );
		}

		if ( serialize( $last_entry ) === serialize( $new_entry ) ) {
			// New error and last error are the same so set the current time and increment the counter.
			$log[0]['date']  = time();
			$log[0]['count'] = isset( $log[0]['count'] ) ? intval( $log[0]['count'] ) + 1 : 2;
		} else {
			// New error is not a repeat to set required fields.
			$new_entry['date']  = time();
			$new_entry['count'] = 1;
			array_unshift( $log, $new_entry );
		}

		return $this->update( $log );
	}

	/**
	 * Clear out the error log.
	 *
	 * @return bool
	 */
	public function clear() {
		return update_option( self::OPTION_NAME, array() );
	}

	/**
	 * Delete the error log option.
	 *
	 * @return bool
	 */
	public function delete() {
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * Update the error log with an array and enforcing the length limit.
	 *
	 * @param array $log - Log array to update.
	 *
	 * @return bool
	 */
	private function update( array $log ) {
		if ( count( $log ) > self::ERROR_LOG_ENTRY_LIMIT ) {
			array_pop( $log );
		}
		return update_option( self::OPTION_NAME, $log );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @deprecated - 3.6.0, not used, handled in WP_Auth0_Admin::admin_enqueue()
	 *
	 * @codeCoverageIgnore
	 */
	public function admin_enqueue() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}
}

/**
 * Function to call the method that clears out the error log.
 *
 * @hook admin_action_wpauth0_clear_error_log
 */
function wp_auth0_errorlog_clear_error_log() {

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'clear_error_log' ) ) {
		wp_die( __( 'Not allowed.', 'wp-auth0' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Not authorized.', 'wp-auth0' ) );
	}

	$error_log = new WP_Auth0_ErrorLog();
	$error_log->clear();

	wp_safe_redirect( admin_url( 'admin.php?page=wpa0-errors&cleared=1' ) );
	exit;
}
