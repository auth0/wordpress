<?php

class WP_Auth0_InitialSetup_ConnectionProfile {

	protected $a0_options;
	protected $domain = 'auth0.auth0.com';

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
		$this->domain     = $this->a0_options->get( 'auth0_server_domain' );
	}

	public function render( $step ) {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/connection_profile.php';
	}

	public function callback() {

		$type = null;

		if ( isset( $_POST['profile-type'] ) ) {
			$type = strtolower( $_POST['profile-type'] );
		}

		if ( isset( $_REQUEST['apitoken'] ) && ! empty( $_REQUEST['apitoken'] ) ) {

			$token  = $_REQUEST['apitoken'];
			$domain = $_REQUEST['domain'];

			$consent_callback = new WP_Auth0_InitialSetup_Consent( $this->a0_options );
			$consent_callback->callback_with_token( $domain, $token, $type, false );

		} else {
			$consent_url = $this->build_consent_url( $type );
			wp_redirect( $consent_url );
		}
		exit();
	}

	public function build_consent_url( $type ) {
		$callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&callback=1' ) );

		$client_id = urlencode( get_bloginfo( 'url' ) );

		$scope = urlencode( implode( ' ', WP_Auth0_Api_Client::ConsentRequiredScopes() ) );

		$url = "https://{$this->domain}/authorize?client_id={$client_id}&response_type=code&redirect_uri={$callback_url}&scope={$scope}&expiration=9999999999&state={$type}";

		return $url;
	}
}
