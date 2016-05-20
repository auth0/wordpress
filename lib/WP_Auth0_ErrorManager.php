<?php

class WP_Auth0_ErrorManager {

	public static function insert_auth0_error( $section, $wp_error ) {

		if ( $wp_error instanceof WP_Error ) {
			$code = $wp_error->get_error_code();
			$message = $wp_error->get_error_message();
		} elseif ( $wp_error instanceof Exception ) {
			$code = $wp_error->getCode();
			$message = $wp_error->getMessage();
		} else {
			$code = null;
			$message = $wp_error;
		}

		global $wpdb;
		$wpdb->insert(
			$wpdb->auth0_error_logs,
			array(
				'section' => $section,
				'date' => date( 'c' ),
				'code' => $code,
				'message' => $message,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

}
