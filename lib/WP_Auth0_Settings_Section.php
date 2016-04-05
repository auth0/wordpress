<?php

class WP_Auth0_Settings_Section {

  protected $a0_options;
  protected $initial_setup;
  protected $users_exporter;
  protected $configure_jwt_auth;
  protected $error_log;
  protected $auth0_admin;
  protected $import_settings;

  public function __construct(WP_Auth0_Options $a0_options, WP_Auth0_InitialSetup $initial_setup, WP_Auth0_Export_Users $users_exporter, WP_Auth0_Configure_JWTAUTH $configure_jwt_auth, WP_Auth0_ErrorLog $error_log, WP_Auth0_Admin $auth0_admin, WP_Auth0_Import_Settings $import_settings) {
    $this->a0_options = $a0_options;
    $this->initial_setup = $initial_setup;
    $this->users_exporter = $users_exporter;
    $this->configure_jwt_auth = $configure_jwt_auth;
    $this->error_log = $error_log;
    $this->auth0_admin = $auth0_admin;
    $this->import_settings = $import_settings;
  }

  public function init(){
    add_action( 'admin_menu', array($this, 'init_menu'), 95.55, 0 );
  }

  public function init_menu() {

    if (isset($_REQUEST['page']) && $_REQUEST['page'] === 'wpa0-help') {
      wp_redirect( admin_url( 'admin.php?page=wpa0#help' ), 301 );
      exit;
    }

    $client_id = $this->a0_options->get('client_id');
    $client_secret = $this->a0_options->get('client_secret');
    $domain = $this->a0_options->get('domain');

    $show_initial_setup = ( ( ! $client_id) || ( ! $client_secret) || ( ! $domain) ) ;

    $main_menu = 'wpa0';

    if ( $show_initial_setup ) {
      $main_menu = 'wpa0-setup';
    }

    add_menu_page( __('Auth0', WPA0_LANG), __('Auth0', WPA0_LANG), 'manage_options', $main_menu,
    ( $show_initial_setup ? array($this->initial_setup, 'render_setup_page') : array($this->auth0_admin, 'render_settings_page') ), WP_Auth0::get_plugin_dir_url() . 'assets/img/a0icon.png', 85.55 );

    if ( $show_initial_setup ) {
      add_submenu_page($main_menu, __('Auth0 for WordPress - Setup Wizard', WPA0_LANG), __('Setup Wizard', WPA0_LANG), 'manage_options', 'wpa0-setup', array($this->initial_setup, 'render_setup_page') );
      add_submenu_page($main_menu, __('Settings', WPA0_LANG), __('Settings', WPA0_LANG), 'manage_options', 'wpa0', array($this->auth0_admin, 'render_settings_page') );
    } else {
      add_submenu_page($main_menu, __('Settings', WPA0_LANG), __('Settings', WPA0_LANG), 'manage_options', 'wpa0', array($this->auth0_admin, 'render_settings_page') );

      add_submenu_page($main_menu, __('Help', WPA0_LANG), __('Help', WPA0_LANG), 'manage_options', 'wpa0-help', array($this, 'redirect_to_help') );

      add_submenu_page($main_menu, __('Auth0 for WordPress - Setup Wizard', WPA0_LANG), __('Setup Wizard', WPA0_LANG), 'manage_options', 'wpa0-setup', array($this->initial_setup, 'render_setup_page') );
    }
    
    add_submenu_page($main_menu, __('Export Users Data', WPA0_LANG), __('Export Users Data', WPA0_LANG), 'manage_options', 'wpa0-users-export', array($this->users_exporter, 'render_export_users') );
    add_submenu_page($main_menu, __('Error Log', WPA0_LANG), __('Error Log', WPA0_LANG), 'manage_options', 'wpa0-errors', array($this->error_log, 'render_settings_page') );
    add_submenu_page($main_menu, __('Import-Export settings', WPA0_LANG), __('Import-Export settings', WPA0_LANG), 'manage_options', 'wpa0-import-settings', array($this->import_settings, 'render_import_settings_page') );

    if (WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled()) {
      add_submenu_page($main_menu, __('JWT Auth integration', WPA0_LANG), __('JWT Auth integration', WPA0_LANG), 'manage_options', 'wpa0-jwt-auth', array($this->configure_jwt_auth, 'render_settings_page') );
    }
  }

  public function redirect_to_help() { 
    
  }
}
