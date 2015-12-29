<?php

class WP_Auth0_InitialSetup_EnterpriseConnection {

  protected $a0_options;

  protected $providers = array(
    array('name' => 'Google Apps', "icon" => 'google', 'url' => 'https://auth0.com/docs/connections/enterprise/google-apps'),
    array('name' => 'Active Directory', "icon" => 'windows', 'url' => 'https://auth0.com/docs/connections/enterprise/active-directory'),
    array('name' => 'SAML-P', "icon" => 'samlp', 'url' => 'https://auth0.com/docs/saml-configuration'/*'https://auth0.com/docs/connections/enterprise/samlp'*/),
    array('name' => 'Azure Active Directory (for Native Apps)', "icon" => 'windows', 'url' => 'https://auth0.com/docs/connections/enterprise/azure-active-directory-native'),
    array('name' => 'ADFS', "icon" => 'windows', 'url' => 'https://auth0.com/docs/connections/enterprise/adfs'),
    array('name' => 'IP Address Authentication', "icon" => 'ip-address', 'url' => null /*'https://auth0.com/docs/connections/enterprise/ip-address'*/),
    array('name' => 'LDAP', "icon" => 'ldap', 'url' => 'https://auth0.com/docs/connections/enterprise/active-directory' /*'https://auth0.com/docs/connections/enterprise/ldap'*/),
    array('name' => 'PingFederate', "icon" => 'ping', 'url' => null /*'https://auth0.com/docs/connections/enterprise/ping-federate'*/),
    array('name' => 'Azure Active Directory', "icon" => 'windows', 'url' => 'https://auth0.com/docs/connections/enterprise/azure-active-directory'),
    array('name' => 'Sharepoint Apps', "icon" => 'sharepoint', 'url' => 'https://auth0.com/docs/connections/enterprise/sharepoint-apps'),
    array('name' => 'WS-Federation', "icon" => 'ws-fed', 'url' => null /*'https://auth0.com/docs/connections/enterprise/ws-fed'*/),
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
