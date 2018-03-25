<?php

class WP_Auth0_InitialSetup_Connections {

  protected $a0_options;
  protected $providers = array(
    array( 'provider' => 'facebook', 'name' => 'Facebook', "icon" => 'Facebook', 'options' => array(
        "public_profile" => true,
        "email" => true,
        "user_birthday" => true,
        "publish_actions" => true,
      ) ),
    array( 'provider' => 'twitter', 'name' => 'Twitter', "icon" => 'Twitter', 'options' => array(
        "profile" => true,
      ) ),
    array( 'provider' => 'google-oauth2', 'name' => 'Google +', "icon" => 'Google', 'options' => array(
        "google_plus" => true,
        "email" => true,
        "profile" => true,
      ) ),
    array( "provider" => 'windowslive', "name" => 'Microsoft Accounts', "icon" => 'Windows LiveID' ),
    array( "provider" => 'yahoo', "name" => 'Yahoo', "icon" => 'Yahoo' ),
    array( "provider" => 'aol', "name" => 'AOL', "icon" => 'Aol' ),
    array( "provider" => 'linkedin', "name" => 'Linkedin', "icon" => 'LinkedIn' ),
    array( "provider" => 'paypal', "name" => 'Paypal', "icon" => 'PayPal' ),
    array( "provider" => 'github', "name" => 'GitHub', "icon" => 'GitHub' ),
    array( "provider" => 'amazon', "name" => 'Amazon', "icon" => 'Amazon' ),
    array( "provider" => 'vkontakte', "name" => 'vkontakte', "icon" => 'vk' ),
    array( "provider" => 'yandex', "name" => 'yandex', "icon" => 'Yandex Metrica' ),
    array( "provider" => 'thirtysevensignals', "name" => 'thirtysevensignals', "icon" => '37signals' ),
    array( "provider" => 'box', "name" => 'box', "icon" => 'Box' ),
    array( "provider" => 'salesforce', "name" => 'salesforce', "icon" => 'Salesforce' ),
    array( "provider" => 'salesforce-sandbox', "name" => 'salesforce-sandbox', "icon" => 'SalesforceSandbox' ),
    array( "provider" => 'salesforce-community', "name" => 'salesforce-community', "icon" => 'SalesforceCommunity' ),
    array( "provider" => 'fitbit', "name" => 'Fitbit', "icon" => 'Fitbit' ),
    array( "provider" => 'baidu', "name" => '百度 (Baidu)', "icon" => 'Baidu' ),
    array( "provider" => 'renren', "name" => '人人 (RenRen)', "icon" => 'RenRen' ),
    array( "provider" => 'weibo', "name" => '新浪微 (Weibo)', "icon" => 'Weibo' ),
    array( "provider" => 'shopify', "name" => 'Shopify', "icon" => 'Shopify' ),
    array( "provider" => 'dwolla', "name" => 'Dwolla', "icon" => 'dwolla' ),
    array( "provider" => 'miicard', "name" => 'miiCard', "icon" => 'miiCard' ),
    array( "provider" => 'wordpress', "name" => 'wordpress', "icon" => 'WordPress' ),
    array( "provider" => 'yammer', "name" => 'Yammer', "icon" => 'Yammer' ),
    array( "provider" => 'soundcloud', "name" => 'soundcloud', "icon" => 'Soundcloud' ),
    array( "provider" => 'instagram', "name" => 'instagram', "icon" => 'Instagram' ),
    array( "provider" => 'evernote', "name" => 'evernote', "icon" => 'Evernote' ),
    array( "provider" => 'evernote-sandbox', "name" => 'evernote-sandbox', "icon" => 'Evernote' ),
    array( "provider" => 'thecity', "name" => 'thecity', "icon" => 'The City' ),
    array( "provider" => 'thecity-sandbox', "name" => 'thecity-sandbox', "icon" => 'The City Sandbox' ),
    array( "provider" => 'planningcenter', "name" => 'planningcenter', "icon" => 'Planning Center' ),
    array( "provider" => 'exact', "name" => 'exact', "icon" => 'Exact' ),

  );

  public function __construct( WP_Auth0_Options $a0_options ) {
    $this->a0_options = $a0_options;

    add_action( 'wp_ajax_a0_initial_setup_set_connection', array( $this, 'update_connection' ) );
  }

	/**
	 * TODO: Not using most of the variables here, remove and cleanup
	 */
  public function render( $step ) {
    wp_enqueue_script( 'wpa0_async', WPA0_PLUGIN_URL . 'assets/lib/async.min.js' );

    $social_connections = array();

    foreach ( $this->providers as $provider ) {
      $social_connections[] = $this->get_social_connection( $provider['provider'], $provider['name'], $provider['icon'] );
    }

    $client_id = $this->a0_options->get( 'client_id' );
    $domain = $this->a0_options->get( 'domain' );

    $db_connection_enabled = $this->a0_options->get( "db_connection_enabled" );
    $db_connection_name = $this->a0_options->get( "db_connection_name" );
    $migration_ws_enabled = $this->a0_options->get( "migration_ws" );
    $next_step = $migration_ws_enabled ? 4 : 3;

    include WPA0_PLUGIN_DIR . 'templates/initial-setup/connections.php';
  }

  protected function get_social_connection( $provider, $name, $icon ) {
    return array(
      'name' => $name,
      'provider' => $provider,
      'icon' => $icon,
      'status' => $this->a0_options->get_connection( "social_{$provider}" ),
      'key' => $this->a0_options->get_connection( "social_{$provider}_key" ),
      'secret' => $this->a0_options->get_connection( "social_{$provider}_secret" ),
    );
  }

  protected function get_provider( $provider_name ) {
    foreach ( $this->providers as $provider ) {
      if ( $provider['provider'] === $provider_name ) {
        return $provider;
      }
    }
  }

  public function update_connection() {

    $provider_name = $_POST["connection"];

    if ( $provider_name == 'auth0' ) {
      $this->toggle_db();
    } else {
      $this->toggle_social( $provider_name );
    }
  }

  protected function toggle_db() {

    $domain = $this->a0_options->get( "domain" );
    $app_token = $this->a0_options->get( "auth0_app_token" );
    $connection_id = $this->a0_options->get( "db_connection_id" );
    $client_id = $this->a0_options->get( "client_id" );

    $connection = WP_Auth0_Api_Client::get_connection( $domain, $app_token, $connection_id );

    $enabled_clients = array();

    if ( $_POST["enabled"] === "true" ) {
      $enabled_clients = $connection->enabled_clients;
      $enabled_clients[] = $client_id;
    } else {
      $enabled_clients = array_diff( $connection->enabled_clients, array( $client_id ) );
    }

    $connection->enabled_clients = array_values($enabled_clients);
 
    unset($connection->name);
    unset($connection->strategy);
    unset($connection->id);

    WP_Auth0_Api_Client::update_connection($domain, $app_token, $connection_id, $connection);

    $this->a0_options->set( "db_connection_enabled" , $_POST["enabled"] === "true" ? 1 : 0 );

    exit;
  }

  protected function toggle_social( $provider_name ) {

    $input = array();
    $old_input = array();

    $operations = new WP_Auth0_Api_Operations( $this->a0_options );

    $provider = $this->get_provider( $provider_name );

    $old_input["social_{$provider_name}"] = $this->a0_options->get_connection( "social_{$provider_name}" );
    $old_input["social_{$provider_name}_key"] = $this->a0_options->get_connection( "social_{$provider_name}_key" );
    $old_input["social_{$provider_name}_secret"] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

    $input["social_{$provider_name}"] = ( $_POST["enabled"] === "true" );
    $input["social_{$provider_name}_key"] = $this->a0_options->get_connection( "social_{$provider_name}_key" );
    $input["social_{$provider_name}_secret"] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

    try {
      $input = $operations->social_validation( $this->a0_options->get( 'auth0_app_token' ), $old_input, $input, $provider_name, isset( $provider['options'] ) ? $provider['options'] : null );
    } catch ( Exception $e ) {
      die( $e->getMessage() );
    }

    foreach ( $input as $key => $value ) {
      $this->a0_options->set_connection( $key, $value );
    }

    exit;
  }

  public function callback() {
    $input = array();
    $old_input = array();

    $operations = new WP_Auth0_Api_Operations( $this->a0_options );

    foreach ( $this->providers as $provider ) {
      $provider_name = $provider['provider'];

      $old_input["social_{$provider_name}"] = $this->a0_options->get_connection( "social_{$provider_name}" );
      $old_input["social_{$provider_name}_key"] = $this->a0_options->get_connection( "social_{$provider_name}_key" );
      $old_input["social_{$provider_name}_secret"] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

      if ( isset( $_REQUEST["social_{$provider_name}"] ) ) {
        $input["social_{$provider_name}"] = $_REQUEST["social_{$provider_name}"];
        $input["social_{$provider_name}_key"] = $_REQUEST["social_{$provider_name}_key"];
        $input["social_{$provider_name}_secret"] = $_REQUEST["social_{$provider_name}_secret"];
      }

      try {
        $input = $operations->social_validation( $this->a0_options->get( 'auth0_app_token' ), $old_input, $input, $provider_name, $provider['options'] );
      } catch ( Exception $e ) {
        die( $e->getMessage() );
      }
    }

    foreach ( $input as $key => $value ) {
      $this->a0_options->set_connection( $key, $value );
    }

    wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5' ) );
  }

  public function add_validation_error( $error ) {
    wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5&error=' . urlencode( 'There was an error setting up your connections.' ) ) );
    exit;
  }
}