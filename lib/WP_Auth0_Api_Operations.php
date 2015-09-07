<?php
class WP_Auth0_Api_Operations {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options){
    $this->a0_options = $a0_options;
  }

  public function enable_users_migration($app_token, $migration_token) {

    $domain = $this->a0_options->get( 'domain' );
    $secret = $this->a0_options->get( 'client_secret' );
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

    $response = WP_Auth0_Api_Client::create_connection($domain, $app_token, array(
      'name' => 'DB-' . str_replace(' ', '-', get_bloginfo('name')),
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

    } elseif($db_connection !== null) {

      $migration_connection_id = $response->id;

      $enabled_clients = array_diff($db_connection->enabled_clients, array($client_id));

      WP_Auth0_Api_Client::update_connection($domain, $app_token,$db_connection->id, array(
        'enabled_clients' => array_values($enabled_clients)
      ));

    }

    return $migration_connection_id;
  }

}
