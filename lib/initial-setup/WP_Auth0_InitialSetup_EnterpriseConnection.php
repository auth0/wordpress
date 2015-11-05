<?php

class WP_Auth0_InitialSetup_EnterpriseConnection {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
  }

  public function render($step) {

    include WPA0_PLUGIN_DIR . 'templates/initial-setup/enterprise_connection.php';
  }

  public function callback() {


    wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );
    
  }
} 
