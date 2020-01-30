<?php

class WP_Auth0_InitialSetup_AdminUser {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/admin-creation.php';
	}

	public function callback() {

		$current_user = wp_get_current_user();

		$data = [
			'client_id'  => $this->a0_options->get( 'client_id' ),
			'email'      => $current_user->user_email,
			'password'   => $_POST['admin-password'],
			'connection' => $this->a0_options->get( 'db_connection_name' ),
		];

		$admin_user = WP_Auth0_Api_Client::signup_user( $this->a0_options->get_auth_domain(), $data );

		if ( $admin_user === false ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wpa0-setup&step=3&result=error' ) );
		} else {

			$admin_user->sub = 'auth0|' . $admin_user->_id;
			unset( $admin_user->_id );

			$user_repo = new WP_Auth0_UsersRepo( WP_Auth0_Options::Instance() );
			$user_repo->update_auth0_object( $current_user->ID, $admin_user );

			wp_safe_redirect( admin_url( 'admin.php?page=wpa0-setup&step=4' ) );
		}
		exit;
	}
}
