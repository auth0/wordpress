<?php

class WP_Auth0_Configure_JWTAUTH {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'admin_action_wpauth0_configure_jwt', array( $this, 'setupjwt' ) );
		add_action( 'plugins_loaded', array( $this, 'check_jwt_auth' ) );
	}

	public function check_jwt_auth() {
		if ( isset( $_REQUEST['page'] ) && 'wpa0-jwt-auth' === $_REQUEST['page'] ) {
			return;
		}

		if ( self::is_jwt_auth_enabled() && ! self::is_jwt_configured() ) {
			add_action( 'admin_notices', array( $this, 'notify_jwt' ) );
		}
	}

	public function notify_jwt() {
?>
		<div class="update-nag">
			JWT Auth installed. To configure it to work the Auth0 plugin, click <a href="admin.php?page=wpa0-jwt-auth">HERE</a>
		</div>
		<?php
	}

	public function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-jwt-auth' !== $_REQUEST['page'] ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/js/bootstrap.min.js' );
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_CSS_URL . 'initial-setup.css' );
		wp_enqueue_style( 'media' );
	}

	public function render_settings_page() {
		$ready = self::is_jwt_configured();
		include WPA0_PLUGIN_DIR . 'templates/configure-jwt-auth.php';
	}

	public function setupjwt() {

		if ( self::is_jwt_auth_enabled() ) {
			JWT_AUTH_Options::set( 'aud', $this->a0_options->get( 'client_id' ) );
			JWT_AUTH_Options::set( 'secret', $this->a0_options->get( 'client_secret' ) );
			JWT_AUTH_Options::set( 'secret_base64_encoded', $this->a0_options->get( 'client_secret_b64_encoded' ) );
			JWT_AUTH_Options::set( 'signing_algorithm', $this->a0_options->get( 'client_signing_algorithm' ) );
			JWT_AUTH_Options::set( 'domain', $this->a0_options->get( 'domain' ) );
			JWT_AUTH_Options::set( 'cache_expiration', $this->a0_options->get( 'cache_expiration' ) );
			JWT_AUTH_Options::set( 'override_user_repo', 'WP_Auth0_UsersRepo' );
			$this->a0_options->set( 'jwt_auth_integration', true );
		}

		wp_redirect( admin_url( 'admin.php?page=wpa0-jwt-auth' ) );

	}

	public static function is_jwt_auth_enabled() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'wp-jwt-auth/JWT_AUTH.php' );
	}

	public static function is_jwt_configured() {
		$options = WP_Auth0_Options::Instance();
		return (
			JWT_AUTH_Options::get( 'aud' ) === $options->get( 'client_id' ) &&
			JWT_AUTH_Options::get( 'secret' ) === $options->get( 'client_secret' ) &&
			JWT_AUTH_Options::get( 'secret_base64_encoded' ) === $options->get( 'client_secret_b64_encoded' ) &&
			JWT_AUTH_Options::get( 'signing_algorithm' ) === $options->get( 'client_signing_algorithm' ) &&
			JWT_AUTH_Options::get( 'domain' ) === $options->get( 'domain' ) &&
			JWT_AUTH_Options::get( 'cache_expiration' ) === $options->get( 'cache_expiration' ) &&
			$options->get( 'jwt_auth_integration' ) &&
			JWT_AUTH_Options::get( 'jwt_attribute' ) === 'sub'
		);
	}

}
