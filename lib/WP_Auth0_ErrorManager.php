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
			$code = 'N/A';
			$message = $wp_error;
		}

		$log = get_option('auth0_error_log', array());

		array_unshift($log, array(
			'section'=>$section,
			'code'=>$code,
			'message'=>$message,
			'date' => time(),
		));

		if (count($log) > 20) {
			array_pop($log);
		}

		update_option( 'auth0_error_log', $log );
	}

}
