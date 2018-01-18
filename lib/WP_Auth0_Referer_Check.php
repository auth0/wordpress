<?php

class WP_Auth0_Referer_Check {
	public static function init() {
		if ( !WP_Auth0_Options::Instance()->get( 'redirect_referer' ) )
			return;

		new WP_Auth0_Referer_Check();
	}

	private function __construct() {
		add_action( 'init', array( $this, 'do_url_check' ) );
		add_filter( 'wp_auth0_get_option', array( $this, 'check_activate' ), 10, 2 );
	}

	public function check_activate( $val, $key ) {
		if ( $key != "active" )
			return $val;

		if ( !isset( $_COOKIE['wp_tt_use_auth0'] ) )
			return 0;

		$is_active = (int)$_COOKIE['wp_tt_use_auth0'];
		return $is_active;
	}

	public function do_url_check() {
		if ( !preg_match( '/\/sso\/?$/i', $_SERVER['REQUEST_URI'] ) )
			return;

		// Set SSO Cookie
		setcookie( 'wp_tt_use_auth0', 1, time()+3600, COOKIEPATH, COOKIE_DOMAIN, false );

		wp_redirect( home_url() );
		exit();
	}
}
