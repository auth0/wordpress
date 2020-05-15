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

		// Null coalescing validates input variable.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		if ( ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ?? '' ), self::SETUP_NONCE_ACTION ) ) {
			wp_nonce_ays( self::SETUP_NONCE_ACTION );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Unauthorized.', 'wp-auth0' ) );
			exit;
		}

		if ( ! empty( $_REQUEST['domain'] ) && ! empty( $_REQUEST['apitoken'] ) ) {

			$token  = sanitize_text_field( wp_unslash( $_REQUEST['apitoken'] ) );
			$domain = sanitize_text_field( wp_unslash( $_REQUEST['domain'] ) );

			$consent_callback = new WP_Auth0_InitialSetup_Consent( $this->a0_options );
			$consent_callback->callback_with_token( $domain, $token, false );

		} else {
			$consent_url = $this->build_consent_url();
			wp_safe_redirect( $consent_url );
		}
		exit();

	}

	public function build_consent_url() {
		return sprintf(
			'https://%s/authorize?client_id=%s&response_type=code&redirect_uri=%s&scope=%s&expiration=9999999999',
			$this->domain,
			urlencode( WP_Auth0_InitialSetup::get_setup_client_id() ),
			urlencode( WP_Auth0_InitialSetup::get_setup_redirect_uri() ),
			urlencode( implode( ' ', WP_Auth0_Api_Client::ConsentRequiredScopes() ) )
		);
	}
}
