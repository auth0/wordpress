<?php

class WP_Auth0_InitialSetup_ConnectionProfile {

	const SETUP_NONCE_ACTION = 'wp_auth0_callback_step1';

	protected $a0_options;
	protected $domain;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
		$this->domain     = $this->a0_options->get( 'auth0_server_domain', 'auth0.auth0.com' );
	}

	public function render( $step ) {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/connection_profile.php';
	}

	public function callback() {

		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], self::SETUP_NONCE_ACTION ) ) {
			wp_nonce_ays( self::SETUP_NONCE_ACTION );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Unauthorized.', 'wp-auth0' ) );
			exit;
		}

		if ( isset( $_REQUEST['apitoken'] ) && ! empty( $_REQUEST['apitoken'] ) ) {

			$token  = $_REQUEST['apitoken'];
			$domain = $_REQUEST['domain'];

			$consent_callback = new WP_Auth0_InitialSetup_Consent( $this->a0_options );
			$consent_callback->callback_with_token( $domain, $token, null, false );

		} else {
			$consent_url = $this->build_consent_url();
			wp_safe_redirect( $consent_url );
		}
		exit();
	}

	public function build_consent_url() {
		$callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&callback=1' ) );

		$client_id = urlencode( get_bloginfo( 'url' ) );

		$scope = urlencode( implode( ' ', WP_Auth0_Api_Client::ConsentRequiredScopes() ) );

		$url = "https://{$this->domain}/authorize?client_id={$client_id}&response_type=code&redirect_uri={$callback_url}&scope={$scope}&expiration=9999999999";

		return $url;
	}
}
