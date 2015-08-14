<?php

class WP_Auth0_Configure_JWTAUTH {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
		add_action( 'admin_action_wpauth0_configure_jwt', array(__CLASS__, 'setupjwt') );
	}

	public static function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-jwt-auth' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();

	}

	public static function render_settings_page() {
		$ready = WP_Auth0::is_jwt_configured();

		include WPA0_PLUGIN_DIR . 'templates/configure-jwt-auth.php';
	}

	public static function setupjwt() {

		$options = WP_Auth0_Options::Instance();
		if ( WP_Auth0::is_jwt_auth_enabled() ) {
			JWT_AUTH_Options::set( 'aud', $options->get( 'client_id' ) );
			JWT_AUTH_Options::set( 'secret', $options->get( 'client_secret' ) );
			JWT_AUTH_Options::set( 'secret_base64_encoded', true );
			JWT_AUTH_Options::set( 'override_user_repo', 'WP_Auth0_UsersRepo' );
			$options->set( 'jwt_auth_integration', true );
		}

		wp_redirect( admin_url( 'admin.php?page=wpa0-jwt-auth' ) );

	}

}
