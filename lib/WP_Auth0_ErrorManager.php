<?php

/**
 * Class WP_Auth0_ErrorManager
 */
class WP_Auth0_ErrorManager {

	/**
	 * Create a row in the error log, up to 20 entries
	 *
	 * @param string                    $section - portion of the codebase that generated the error
	 * @param string|WP_Error|Exception $error - error message string or discoverable error type
	 */
	public static function insert_auth0_error( $section, $error ) {
		$code    = 'unknown_code';
		$message = __( 'Unknown error message', 'wp-auth0' );

		if ( $error instanceof WP_Error ) {
			$code    = $error->get_error_code();
			$message = $error->get_error_message();
		} elseif ( $error instanceof Exception ) {
			$code    = $error->getCode();
			$message = $error->getMessage();
		} elseif ( is_array( $error ) && ! empty( $error['response'] ) ) {
			if ( ! empty( $error['response']['code'] ) ) {
				$code = sanitize_text_field( $error['response']['code'] );
			}
			if ( ! empty( $error['response']['message'] ) ) {
				$message = sanitize_text_field( $error['response']['message'] );
			}
		} else {
			$message = is_object( $error ) || is_array( $error ) ? serialize( $error ) : $error;
		}

		$log = get_option( 'auth0_error_log' );

		if ( empty( $log ) ) {
			$log = array();
		}

		array_unshift(
			$log, array(
				'section' => $section,
				'code'    => $code,
				'message' => $message,
				'date'    => time(),
			)
		);

		if ( count( $log ) > 20 ) {
			array_pop( $log );
		}

		update_option( 'auth0_error_log', $log );
	}
}
