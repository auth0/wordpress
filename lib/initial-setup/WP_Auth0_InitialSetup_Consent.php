<?php

class WP_Auth0_InitialSetup_Consent {

  protected $domain = 'login0.myauth0.com';
  protected $client_id = 'vGzHpD0XGHAlR1JIECGbFVuCKTCECUt4';
  protected $client_secret = '8U1joJzZwVb5EBa6PS4zCsZW1RbRaz6cvxuDYxCeCzXNdAwbikqh7VrzUuBduS0r';

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
  }

  public function render($step) {
    $consent_url = $this->build_consent_url();
    include WPA0_PLUGIN_DIR . 'templates/initial-setup/consent-disclaimer.php';
  }

  public function callback() {
    $sucess = $this->store_token_domain();

    if ( ! $sucess ) {
      wp_redirect( admin_url( 'admin.php?page=wpa0-setup&error=cant_exchange_token' ) );
      exit;
    }

    $name = get_bloginfo('name');
    $this->consent_callback($name);
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

    $response = WP_Auth0_Api_Client::get_token( $this->domain, $this->client_id, $this->client_secret, 'authorization_code', array(
            'redirect_uri' => home_url(),
            'code' => $code,
        ) );

    $obj = json_decode($response['body']);

    if (isset($obj->error)) {
        return null;
    }

    return $obj->access_token;
  }

  public function store_token_domain() {
    $app_token = $this->exchange_code();

    if ($app_token === null) {
        return false;
    }

    $app_domain = $this->parse_token_domain($app_token);

    $this->a0_options->set( 'auth0_app_token', $app_token );
    $this->a0_options->set( 'domain', $app_domain );

    return true;
  }

  public function consent_callback($name) {

    $app_token = $this->a0_options->get( 'auth0_app_token' );
    $domain = $this->a0_options->get( 'domain' );

    $response = WP_Auth0_Api_Client::create_client($domain, $app_token, $name);

    if ($response === false) {
        wp_redirect( admin_url( 'admin.php?page=wpa0&error=cant_create_client' ) );
        exit;
    }

    $this->a0_options->set( 'client_id', $response->client_id );
    $this->a0_options->set( 'client_secret', $response->client_secret );

    $connections = WP_Auth0_Api_Client::search_connection($domain, $app_token);

    $enabled_connections = $this->a0_options->get_enabled_connections();

    foreach ($connections as $connection) {
        if ( in_array( $connection->name, $enabled_connections ) ) {

            $this->a0_options->set( "social_{$connection->name}" , 1 );
            $this->a0_options->set( "social_{$connection->name}_key" , isset($connection->options->client_id) ? $connection->options->client_id : null );
            $this->a0_options->set( "social_{$connection->name}_secret" , isset($connection->options->client_secret) ? $connection->options->client_secret : null );

        }
    }

    wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );
    exit();

  }

  public function build_consent_url() {
    $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&callback=1' ) );

    $scope = urlencode( implode( ' ', array(
        'read:connections',
        'create:connections',
        'update:connections',
        'create:clients'
    ) ) );

    $url = "https://{$this->domain}/authorize?client_id={$this->client_id}&response_type=code&redirect_uri={$callback_url}&scope={$scope}";

    return $url;
  }

}
