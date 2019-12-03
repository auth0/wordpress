<?php
class WP_Auth0_Api_Operations {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function create_wordpress_connection( $app_token, $migration_enabled, $password_policy = '', $migration_token = null ) {

		$domain             = $this->a0_options->get( 'domain' );
		$client_id          = $this->a0_options->get( 'client_id' );
		$db_connection_name = 'DB-' . get_auth0_curatedBlogName();

		$body = [
			'name'            => $db_connection_name,
			'strategy'        => 'auth0',
			'options'         => [
				'passwordPolicy' => $password_policy,
			],
			'enabled_clients' => [
				$client_id,
			],
		];

		if ( $migration_enabled ) {

			$ipCheck = new WP_Auth0_Ip_Check();
			$ips     = $ipCheck->get_ips_by_domain( $domain );

			if ( $ips ) {
				$this->a0_options->set( 'migration_ips', $ips );
				$this->a0_options->set( 'migration_ips_filter', true );
			} else {
				$this->a0_options->set( 'migration_ips', null );
				$this->a0_options->set( 'migration_ips_filter', false );
			}

			$body['options'] = [
				'enabledDatabaseCustomization' => true,
				'requires_username'            => true,
				'import_mode'                  => true,
				'passwordPolicy'               => $password_policy,
				'brute_force_protection'       => true,
				'validation'                   => [
					'username' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'customScripts'                => [
					'login'    => $this->get_script( 'login' ),
					'get_user' => $this->get_script( 'get-user' ),
				],
				'bareConfiguration'            => [
					'endpointUrl'    => site_url( 'index.php?a0_action=' ),
					'migrationToken' => $migration_token,
					'userNamespace'  => 'DB-' . get_auth0_curatedBlogName(),
				],
			];

		}

		$this->a0_options->set( 'db_connection_name', $db_connection_name );

		$response = WP_Auth0_Api_Client::create_connection( $domain, $app_token, $body );

		if ( $response === false ) {
			return false;
		}

		return $response->id;
	}


	/**
	 * Get JS to use in the custom database script.
	 *
	 * @param string $name - Database script name.
	 *
	 * @return string
	 */
	protected function get_script( $name ) {
		return (string) file_get_contents( WPA0_PLUGIN_DIR . 'lib/scripts-js/db-' . $name . '.js' );
	}
}
