<?php

class WP_Auth0_InitialSetup_Consent {

  protected $domain = 'auth0.auth0.com';

  protected $a0_options;
  protected $state;
  protected $hasInternetConnection = true;

  public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
  }

  public function render($step) {
  }

  public function callback_with_token($domain, $access_token, $type, $hasInternetConnection = true) { 

    $this->a0_options->set( 'auth0_app_token', $access_token );
    $this->a0_options->set( 'domain', $domain );

    $this->hasInternetConnection = $hasInternetConnection;

    $this->state = $type;

    if ( ! in_array($this->state, array('social', 'enterprise') ) ) {
      wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=invalid_state' ) );
      exit;
    }

    $this->a0_options->set( "account_profile" , $this->state );

    $name = get_bloginfo('name');
    $this->consent_callback($name);

  }

  public function callback() {

    $access_token = $this->exchange_code();

    if ($access_token === null) {
      wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=cant_exchange_token' ) );
      exit;
    }

    $app_domain = $this->parse_token_domain($access_token);

    if ( ! isset($_REQUEST['state']) ) {
      wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=missing_state' ) );
      exit;
    }

    $this->callback_with_token($app_domain, $access_token, $_REQUEST['state']);
  }

  protected function parse_token_domain($token) {
    $parts = explode('.', $token);
    $payload = json_decode( JWT::urlsafeB64Decode( $parts[1] ) );
    return trim(str_replace( array('/api/v2', 'https://'), '', $payload->aud ), ' /');
  }

  public function exchange_code() {
    if ( ! isset($_REQUEST['code']) ) {
        return null;
    }

    $code = $_REQUEST['code'];
    $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );

    $client_id = get_bloginfo('wpurl');

    $response = WP_Auth0_Api_Client::get_token( $this->domain, $client_id, null, 'authorization_code', array(
            'redirect_uri' => home_url(),
            'code' => $code,
        ) );

    $obj = json_decode($response['body']);

    if (isset($obj->error)) {
        return null;
    }

    return $obj->access_token;
  }

  public function consent_callback($name) {

    $app_token = $this->a0_options->get( 'auth0_app_token' );
    $domain = $this->a0_options->get( 'domain' );

    $client_id = trim($this->a0_options->get( 'client_id' ));

    $should_create_and_update_connection = false;

    if (empty($client_id)) {
      $should_create_and_update_connection = true;

      $client_response = WP_Auth0_Api_Client::create_client($domain, $app_token, $name);

      if ($client_response === false) {
          wp_redirect( admin_url( 'admin.php?page=wpa0&error=cant_create_client' ) );
          exit;
      }

      $this->a0_options->set( 'client_id', $client_response->client_id );
      $this->a0_options->set( 'client_secret', $client_response->client_secret );

      $client_id = $client_response->client_id;
    }

    $db_connection_name = 'DB-' . str_replace(' ', '-', get_bloginfo('name'));
    $connection_exists = false;
    $connection_pwd_policy = false;

    $connections = WP_Auth0_Api_Client::search_connection($domain, $app_token);

    foreach ($connections as $connection) {

      if ( in_array( $client_id, $connection->enabled_clients ) ) {
        if ( $connection->strategy === 'auth0' && $should_create_and_update_connection) {

          if ($db_connection_name === $connection->name) {
            $connection_exists = $connection->id;
            $connection_pwd_policy = (isset($connection->options) && isset($connection->options->passwordPolicy)) ? $connection->options->passwordPolicy : null;
          } else {
            $enabled_clients = array_diff($connection->enabled_clients, array($client_id));
            WP_Auth0_Api_Client::update_connection($domain, $app_token, $connection->id, array('enabled_clients' => array_values($enabled_clients)));
          }

				} elseif ($connection->strategy !== 'auth0') {
          $this->a0_options->set_connection( "social_{$connection->name}" , 1 );
          $this->a0_options->set_connection( "social_{$connection->name}_key" , isset($connection->options->client_id) ? $connection->options->client_id : null );
          $this->a0_options->set_connection( "social_{$connection->name}_secret" , isset($connection->options->client_secret) ? $connection->options->client_secret : null );
        }
      }
    }

    if ($should_create_and_update_connection) {

      if ($connection_exists === false) {

        $secret = $this->a0_options->get( 'client_secret' );
        $token_id = uniqid();
        $migration_token = JWT::encode(array('scope' => 'migration_ws', 'jti' => $token_id), JWT::urlsafeB64Decode( $secret ));
        $migration_token_id = $token_id;

        $operations = new WP_Auth0_Api_Operations($this->a0_options);
        $response = $operations->create_wordpress_connection($this->a0_options->get( 'auth0_app_token' ), $this->hasInternetConnection, $migration_token);

        $this->a0_options->set( "migration_ws" , $this->hasInternetConnection );
        $this->a0_options->set( "migration_token" , $migration_token );
        $this->a0_options->set( "migration_token_id" , $migration_token_id );
        $this->a0_options->set( "db_connection_enabled" , $response ? 1 : 0 );
        $this->a0_options->set( "db_connection_id" , $response );
        $this->a0_options->set( "password_policy" , null );

      } else {

        $this->a0_options->set( "db_connection_enabled" , 1 );
        $this->a0_options->set( "db_connection_id" , $connection_exists );
        $this->a0_options->set( "password_policy" , $connection_pwd_policy );

      }

      
    }
    

    wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=2&profile=' . $this->state ) );
    exit();

  }

}
