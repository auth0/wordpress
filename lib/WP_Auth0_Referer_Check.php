<?php
/**
 * @deprecated - 3.8.0, not used and no replacement provided.
 *
 * @codeCoverageIgnore - Deprecated
 */
class WP_Auth0_Referer_Check {
	public static function init() {
		if ( ! WP_Auth0_Options::Instance()->get( 'redirect_referer' ) ) {
			return;
		}

		new WP_Auth0_Referer_Check();
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 */
	private function __construct() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		add_action( 'init', array( $this, 'do_url_check' ) );
		add_filter( 'wp_auth0_get_option', array( $this, 'check_activate' ), 10, 2 );
	}

	public function check_activate( $val, $key ) {
		if ( $key != 'active' ) {
			return $val;
		}

		if ( ! isset( $_COOKIE['wp_tt_use_auth0'] ) ) {
			return 0;
		}

		$is_active = (int) $_COOKIE['wp_tt_use_auth0'];
		return $is_active;
	}

	public function do_url_check() {
		if ( ! preg_match( '/\/sso\/?$/i', $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		// Set SSO Cookie
		setcookie( 'wp_tt_use_auth0', 1, time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false );

		wp_redirect( home_url() );
		exit();
	}
}
