<?php

class WP_Auth0_InitialSetup_ConnectionProfile {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
  }

  public function render($step) {

    include WPA0_PLUGIN_DIR . 'templates/initial-setup/connection_profile.php';
  }

  public function callback() {

    $type = null;

    if (isset($_POST['type'])) {
      $type = strtolower( $_POST['type'] );
    } 

    switch ($type) {
      case 'social':
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=3' ) );
        exit;
      case 'enterprise':
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=4' ) );
        exit;
      default:
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );
        exit;
    }
    
  }
} 
