<?php

class WP_Auth0_InitialSetup_EnterpriseConnection {

  protected $a0_options;

  protected $providers = array(
    array('name' => 'Google Apps', "icon" => 'google'),
    array('name' => 'Active Directory', "icon" => 'windows'),
    array('name' => 'SAML-P', "icon" => 'samlp'),
    array('name' => 'Azure Active Directory (for Native Apps)', "icon" => 'windows'),
    array('name' => 'ADFS', "icon" => 'windows'),
    array('name' => 'IP Address Authentication', "icon" => 'ip-address'),
    array('name' => 'LDAP', "icon" => 'ldap'),
    array('name' => 'PingFederate', "icon" => 'ping'),
    array('name' => 'Azure Active Directory', "icon" => 'windows'),
    array('name' => 'Sharepoint Apps', "icon" => 'sharepoint'),
    array('name' => 'WS-Federation', "icon" => 'ws-fed'),
  );

  public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
  }

  public function render($step) {
    $providers = $this->providers;

    include WPA0_PLUGIN_DIR . 'templates/initial-setup/enterprise_connections.php';
  }

  public function callback() {


    wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=2' ) );
    
  }
} 
