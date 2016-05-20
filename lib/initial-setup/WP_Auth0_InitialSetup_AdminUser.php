<?php

class WP_Auth0_InitialSetup_AdminUser {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		$client_id = $this->a0_options->get( 'client_id' );
		$domain = $this->a0_options->get( 'domain' );
		$current_user = wp_get_current_user();
		$error = isset( $_REQUEST['result'] ) && $_REQUEST['result'] === 'error';
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/admin-creation.php';
	}

	public function callback() {

		$current_user = wp_get_current_user();

		$db_connection_name = $this->a0_options->get( "db_connection_name" );
		$domain = $this->a0_options->get( 'domain' );
		$jwt = $this->a0_options->get( 'auth0_app_token' );

		$data = array(
			'email' => $current_user->user_email,
			'password' => $_POST['admin-password'],
			'connection' => $db_connection_name,
			'email_verified' => true
		);

		$admin_user = WP_Auth0_Api_Client::create_user( $domain, $jwt, $data );

		if ( $admin_user === false ) {
			wp_redirect( admin_url( "admin.php?page=wpa0-setup&step=3&profile=social&result=error" ) );
		}
		else {
			wp_redirect( admin_url( "admin.php?page=wpa0-setup&step=4&profile=social" ) );
		}
		exit;
	}

}
