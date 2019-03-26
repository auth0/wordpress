<?php

class WP_Auth0_InitialSetup_Connections {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/connections.php';
	}

	public function callback() {
		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5' ) );
	}

	public function add_validation_error( $error ) {
		wp_redirect(
			admin_url(
				'admin.php?page=wpa0-setup&step=5&error=' .
				urlencode( 'There was an error setting up your connections.' )
			)
		);
		exit;
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function update_connection() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$provider_name = $_POST['connection'];

		if ( $provider_name == 'auth0' ) {
			$this->toggle_db();
		} else {
			$this->toggle_social( $provider_name );
		}
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	protected function toggle_db() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$domain        = $this->a0_options->get( 'domain' );
		$app_token     = $this->a0_options->get( 'auth0_app_token' );
		$connection_id = $this->a0_options->get( 'db_connection_id' );
		$client_id     = $this->a0_options->get( 'client_id' );

		$connection = WP_Auth0_Api_Client::get_connection( $domain, $app_token, $connection_id );

		$enabled_clients = array();

		if ( $_POST['enabled'] === 'true' ) {
			$enabled_clients   = $connection->enabled_clients;
			$enabled_clients[] = $client_id;
		} else {
			$enabled_clients = array_diff( $connection->enabled_clients, array( $client_id ) );
		}

		$connection->enabled_clients = array_values( $enabled_clients );

		unset( $connection->name );
		unset( $connection->strategy );
		unset( $connection->id );

		WP_Auth0_Api_Client::update_connection( $domain, $app_token, $connection_id, $connection );

		$this->a0_options->set( 'db_connection_enabled', $_POST['enabled'] === 'true' ? 1 : 0 );

		exit;
	}

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	protected function toggle_social( $provider_name ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$provider_options = array(
			'facebook'      => array(
				'public_profile'  => true,
				'email'           => true,
				'user_birthday'   => true,
				'publish_actions' => true,
			),
			'twitter'       => array(
				'profile' => true,
			),
			'google-oauth2' => array(
				'google_plus' => true,
				'email'       => true,
				'profile'     => true,
			),
		);

		$input     = array();
		$old_input = array();

		$operations = new WP_Auth0_Api_Operations( $this->a0_options );

		$old_input[ "social_{$provider_name}" ]        = $this->a0_options->get_connection( "social_{$provider_name}" );
		$old_input[ "social_{$provider_name}_key" ]    = $this->a0_options->get_connection( "social_{$provider_name}_key" );
		$old_input[ "social_{$provider_name}_secret" ] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

		$input[ "social_{$provider_name}" ]        = ( $_POST['enabled'] === 'true' );
		$input[ "social_{$provider_name}_key" ]    = $this->a0_options->get_connection( "social_{$provider_name}_key" );
		$input[ "social_{$provider_name}_secret" ] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

		try {
			$options = isset( $provider_options[ $provider_name ] ) ? $provider_options[ $provider_name ] : null;
			$input   = $operations->social_validation( $this->a0_options->get( 'auth0_app_token' ), $old_input, $input, $provider_name, $options );
		} catch ( Exception $e ) {
			exit( $e->getMessage() );
		}

		foreach ( $input as $key => $value ) {
			$this->a0_options->set_connection( $key, $value );
		}

		exit;
	}
}
