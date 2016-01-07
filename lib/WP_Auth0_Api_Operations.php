<?php
class WP_Auth0_Api_Operations {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options){
    $this->a0_options = $a0_options;
  }

  public function enable_users_migration($app_token, $migration_token) {

    $domain = $this->a0_options->get( 'domain' );
    $client_id = $this->a0_options->get( 'client_id' );

    $connections = WP_Auth0_Api_Client::search_connection($domain, $app_token, 'auth0');
    $db_connection = null;

    foreach($connections as $connection) {
        if (in_array($client_id, $connection->enabled_clients)) {
          $db_connection = $connection;
        }
    }

    $login_script = str_replace('{THE_WS_TOKEN}', $migration_token, WP_Auth0_CustomDBLib::$login_script);
    $login_script = str_replace('{THE_WS_URL}', get_site_url() . '/migration-ws-login', $login_script);

    $get_user_script = str_replace('{THE_WS_TOKEN}', $migration_token, WP_Auth0_CustomDBLib::$get_user_script);
    $get_user_script = str_replace('{THE_WS_URL}', get_site_url() . '/migration-ws-get-user', $get_user_script);

    $db_connection_name = 'DB-' . str_replace(' ', '-', get_bloginfo('name'));

    $this->a0_options->set( "db_connection_name", $db_connection_name );

    $response = WP_Auth0_Api_Client::create_connection($domain, $app_token, array(
      'name' => $db_connection_name,
      'strategy' => 'auth0',
      'enabled_clients' => array(
        $client_id
      ),
      'options' => array(
        'enabledDatabaseCustomization' => true,
        'import_mode' => true,
        'customScripts' => array(
          'login' => $login_script,
          'get_user' => $get_user_script
        )
      )
    ));

    if ($response === false) {

      return false;

    }

    $migration_connection_id = $response->id;

    if($db_connection !== null) {

      $enabled_clients = array_diff($db_connection->enabled_clients, array($client_id));

      WP_Auth0_Api_Client::update_connection($domain, $app_token,$db_connection->id, array(
        'enabled_clients' => array_values($enabled_clients)
      ));

    }

    return $migration_connection_id;
  }

  /**
	 * This function will sync and update the connection setting with auth0
	 * First it checks if there is any connection with this strategy enabled for the app.
	 * - If exists, it checks if it has the facebook keys, in this case will ignore WP setting and will update the WP settings
	 * - If exists, it checks if it has the facebook keys, if not, it will update the connection with the new keys
	 *
	 * - If not exists, it will create a new connection
	 *
	 * In the case that the user disable the connection on WP, it check if there is an active connection with the client_id.
	 * - If exists, it will remove the client_id and if there is no other client_id it will delete the connection.
	 */
	public function social_validation( $app_token, $old_options, $input, $strategy, $connection_options ) {
    $domain = $this->a0_options->get( 'domain' );
    $secret = $this->a0_options->get( 'client_secret' );
    $client_id = $this->a0_options->get( 'client_id' );

		$main_key = "social_$strategy";

		$input[$main_key] = ( isset( $input[$main_key] ) ? $input[$main_key]  : 0);
		$input["{$main_key}_key"] = ( empty( $input["{$main_key}_key"] ) ? null  : trim( $input["{$main_key}_key"] ) );
		$input["{$main_key}_secret"] = ( empty( $input["{$main_key}_secret"] ) ? null  : trim( $input["{$main_key}_secret"] ) );

		if (
			$old_options[$main_key] != $input[$main_key] ||
			$old_options["{$main_key}_key"] != $input["{$main_key}_key"] ||
			$old_options["{$main_key}_secret"] != $input["{$main_key}_secret"]
			) {

			$connections = WP_Auth0_Api_Client::search_connection($domain, $app_token, $strategy);

			// if ( ! $connections ) {
			// 	$error = __( 'There was an error searching your active social connections.', WPA0_LANG );
			// 	$this->add_validation_error( $error );
			//
			// 	$input[$main_key] = 0;
			//
			// 	return $input;
			// }

			$selected_connection = null;

			foreach ($connections as $connection) {
				if (in_array($client_id, $connection->enabled_clients)) {
					$selected_connection = $connection;
					break;
				} elseif ( ! $selected_connection && count($connection->enabled_clients) == 0 ) {
					$selected_connection = $connection;
					$selected_connection->enabled_clients[] = $client_id;
				} elseif ( $connection->name == 'facebook' ) {
					$selected_connection = $connection;
					$selected_connection->enabled_clients[] = $client_id;
				}
			}
			if ( $selected_connection === null && count($connections) === 1) {
				$selected_connection = $connections[0];
				$selected_connection->enabled_clients[] = $client_id;
			}

      if (empty($connection_options)) {
        $connection_options = array();
      }
      
			if ( $input[$main_key] ) {

				if ( $selected_connection &&
          ( empty($selected_connection->options->client_id) || (empty($input["{$main_key}_key"]) && empty($old_input["{$main_key}_key"])) || $selected_connection->options->client_id === $input["{$main_key}_key"] ) &&
          ( empty($selected_connection->options->client_secret) || (empty($input["{$main_key}_secret"]) && empty($old_input["{$main_key}_secret"])) || $selected_connection->options->client_secret === $input["{$main_key}_secret"] ) ) {

          $data = array(
            'options' => $connection_options,
            'enabled_clients' => $connection->enabled_clients
          );

          if ( !empty($input["{$main_key}_key"]) && !empty($input["{$main_key}_secret"]) ) {
            $data['options']['client_id'] = $input["{$main_key}_key"];
            $data['options']['client_secret'] = $input["{$main_key}_secret"];
          }

          $response = WP_Auth0_Api_Client::update_connection($domain, $app_token, $selected_connection->id, $data);

					if ( false === $response ) {
						$error = __( 'There was an error updating your social connection', WPA0_LANG );
						throw new Exception( $error );

						$input[$main_key] = 0;

						return $input;
					}
				} elseif ( $selected_connection && !empty($selected_connection->options->client_id) && !empty($selected_connection->options->client_secret) 
            && !empty($input["{$main_key}_key"]) && !empty($input["{$main_key}_secret"]) ) {

					$input["{$main_key}_key"] = $selected_connection->options->client_id;
					$input["{$main_key}_secret"] = $selected_connection->options->client_secret;

					$error = 'The connection has already setted an api key and secret and can not be overrided. Please update them from the <a href="https://manage.auth0.com/#/connections/social">Auth0 dashboard</a>';
          throw new Exception( $error );

					$data = array(
						'options' => array_merge($connection_options, array(
							"client_id" => $input["{$main_key}_key"],
      						"client_secret" => $input["{$main_key}_secret"],
						) ),
						'enabled_clients' => $connection->enabled_clients
					);

          $response = WP_Auth0_Api_Client::update_connection($domain, $app_token, $selected_connection->id, $data);

					if ( false === $response ) {
						$error = __( 'There was an error updating your social connection', WPA0_LANG );
						throw new Exception( $error );

						$input[$main_key] = 0;

						return $input;
					}

				} elseif ( ! $selected_connection ) {

					$data = array(
						'name' => $strategy,
						'strategy' => $strategy,
						'enabled_clients' => array( $client_id ),
						'options' => array_merge($connection_options, array(
							"client_id" => $input["{$main_key}_key"],
      						"client_secret" => $input["{$main_key}_secret"],
						) ),
					);

					if ( false === WP_Auth0_Api_Client::create_connection($domain, $app_token, $data) ) {
						$error = __( 'There was an error creating your social connection', WPA0_LANG );
						throw new Exception( $error );

						$input[$main_key] = 0;

						return $input;
					}
				}

			}
			else {
				if ($selected_connection) {
					$data['enabled_clients'] = array();
					foreach ($selected_connection->enabled_clients as $client) {
						if ($client != $client_id) {
							$data['enabled_clients'][] = $client;
						}
					}

					if ( false === $a = WP_Auth0_Api_Client::update_connection($domain, $app_token, $selected_connection->id, $data) ) {
						$error = __( 'There was an error disabling your social connection for this app.', WPA0_LANG );
						throw new Exception( $error );
						$input[$main_key] = 1;
					}
				}
			}

		}

		return $input;
	}

  //$input['geo_rule'] = ( isset( $input['geo_rule'] ) ? $input['geo_rule'] : 0 );
  //$enable = ($old_options['geo_rule'] == null && 1 == $input['geo_rule'])
  public function toggle_rule ($app_token, $rule_id, $rule_name, $rule_script) {
    $domain = $this->a0_options->get( 'domain' );
		if (is_null($rule_id)) {
			$rule = WP_Auth0_Api_Client::create_rule( $domain, $app_token, $rule_name, $rule_script );

			if ( $rule === false ) {
				$error = __( 'There was an error creating the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
        throw new Exception( $error );
			} else {
				return $rule->id;
			}
		}
		else {
			if ( false === WP_Auth0_Api_Client::delete_rule($domain, $app_token, $rule_id) ) {
				$error = __( 'There was an error deleting the Auth0 rule. You can do it manually from your Auth0 dashboard.', WPA0_LANG );
        throw new Exception( $error );
			}
      return null;
		}
  }


}
