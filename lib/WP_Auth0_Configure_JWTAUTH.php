<?php

class WP_Auth0_Configure_JWTAUTH {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
	}

	public static function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-jwt-auth' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wpa0-jwt-auth', WPA0_PLUGIN_URL . 'assets/css/settings.css' );

	}

	public static function render_settings_page() {

		if ( isset( $_POST['setup'] ) && 'Yes' === $_POST['setup'] ) {
			self::setupjwt();
		}

		$ready = WP_Auth0::is_jwt_configured();

		include WPA0_PLUGIN_DIR . 'templates/configure-jwt-auth.php';
	}

	protected static function setupjwt() {
		if ( WP_Auth0::is_jwt_auth_enabled() ) {
			JWT_AUTH_Options::set( 'aud', WP_Auth0_Options::get( 'client_id' ) );
			JWT_AUTH_Options::set( 'secret', WP_Auth0_Options::get( 'client_secret' ) );
			JWT_AUTH_Options::set( 'secret_base64_encoded', true );
			JWT_AUTH_Options::set( 'override_user_repo', 'WP_Auth0_UsersRepo' );
			WP_Auth0_Options::set( 'jwt_auth_integration', true );
		}
	}

}
