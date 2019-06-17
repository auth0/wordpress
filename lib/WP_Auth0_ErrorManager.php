<?php
/**
 * Contains the WP_Auth0_ErrorManager class.
 *
 * @package WP-Auth0
 */

/**
 * Class WP_Auth0_ErrorManager.
 * Handles creating a new error log entry.
 */
class WP_Auth0_ErrorManager {

	/**
	 * Create a row in the error log.
	 *
	 * @param string $section - Portion of the codebase that generated the error.
	 * @param mixed  $error - Error message string or discoverable error type.
	 *
	 * @return bool
	 */
	public static function insert_auth0_error( $section, $error ) {

		$new_entry = [
			'section' => $section,
			'code'    => 'unknown_code',
			'message' => __( 'Unknown error message', 'wp-auth0' ),
		];

		if ( $error instanceof WP_Error ) {
			$new_entry['code']    = $error->get_error_code();
			$new_entry['message'] = $error->get_error_message();
		} elseif ( $error instanceof Exception ) {
			$new_entry['code']    = $error->getCode();
			$new_entry['message'] = $error->getMessage();
		} elseif ( is_array( $error ) && ! empty( $error['response'] ) ) {
			if ( ! empty( $error['response']['code'] ) ) {
				$new_entry['code'] = sanitize_text_field( $error['response']['code'] );
			}
			if ( ! empty( $error['response']['message'] ) ) {
				$new_entry['message'] = sanitize_text_field( $error['response']['message'] );
			}
		} else {
			$new_entry['message'] = is_object( $error ) || is_array( $error ) ? serialize( $error ) : $error;
		}

		$error_log = new WP_Auth0_ErrorLog();
		return $error_log->add( $new_entry );
	}
}
