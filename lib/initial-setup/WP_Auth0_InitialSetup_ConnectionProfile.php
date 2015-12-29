<?php

class WP_Auth0_InitialSetup_ConnectionProfile {

  protected $a0_options;
  protected $domain = 'auth0.auth0.com';

  public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
  }

  public function render($step) {

    include WPA0_PLUGIN_DIR . 'templates/initial-setup/connection_profile.php';
  }

  public function callback() {

    $type = null;

    if (isset($_POST['profile-type'])) {
      $type = strtolower( $_POST['profile-type'] );
    } 

    $consent_url = $this->build_consent_url($type);

    wp_redirect($consent_url);
    exit();
  }

  public function build_consent_url($type) {
    $callback_url = urlencode( admin_url( 'admin.php?page=wpa0-setup&callback=1' ) );

    $client_id = urlencode(get_bloginfo('wpurl'));

    $scope = urlencode( implode( ' ', array(
      'create:clients',
      'update:clients',
      'update:connections',
      'create:connections',
      'read:connections',
      'create:rules',
      'delete:rules',
      'update:users',
      'create:users',
    ) ) );

    $url = "https://{$this->domain}/i/oauth2/authorize?client_id={$client_id}&response_type=code&redirect_uri={$callback_url}&scope={$scope}&expiration=9999999999&state={$type}";

    return $url;
  }
} 
