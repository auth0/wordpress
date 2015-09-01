<?php

class WP_Auth0_Settings_Section {

    protected $a0_options;
    protected $initial_setup;
    protected $users_exporter;
    protected $configure_jwt_auth;
    protected $error_log;
    protected $dashboard_preferences;
    protected $auth0_admin;

    public function __construct(WP_Auth0_Options $a0_options, WP_Auth0_InitialSetup $initial_setup, WP_Auth0_Export_Users $users_exporter, WP_Auth0_Configure_JWTAUTH $configure_jwt_auth, WP_Auth0_ErrorLog $error_log, WP_Auth0_Dashboard_Preferences $dashboard_preferences, WP_Auth0_Admin $auth0_admin) {
      $this->a0_options = $a0_options;
      $this->initial_setup = $initial_setup;
      $this->users_exporter = $users_exporter;
      $this->configure_jwt_auth = $configure_jwt_auth;
      $this->error_log = $error_log;
      $this->dashboard_preferences = $dashboard_preferences;
      $this->auth0_admin = $auth0_admin;
    }

    public function init(){
        add_action( 'admin_menu', array($this, 'init_menu'), 95.55, 0 );
    }

    public function init_menu(){

        $auth0_app_token = $this->a0_options->get('auth0_app_token');
        $client_id = $this->a0_options->get('client_id');

        $show_initial_setup = ! ( $auth0_app_token && $client_id );

        $main_menu = 'wpa0';

        if ( $show_initial_setup ) {
            $main_menu = 'wpa0-setup';
        }

        add_menu_page( __('Auth0', WPA0_LANG), __('Auth0', WPA0_LANG), 'manage_options', $main_menu,
            ( $show_initial_setup ? array($this->initial_setup, 'render_setup_page') : array($this->auth0_admin, 'render_settings_page') ), WP_Auth0::get_plugin_dir_url() . 'assets/img/a0icon.png', 85.55 );

        if ( $show_initial_setup ) {
            add_submenu_page($main_menu, __('Auth0 for WordPress - Quick Start Guide', WPA0_LANG), __('Quick Start Guide', WPA0_LANG), 'manage_options', 'wpa0-setup', array($this->initial_setup, 'render_setup_page') );
        }

        add_submenu_page($main_menu, __('Settings', WPA0_LANG), __('Settings', WPA0_LANG), 'manage_options', 'wpa0', array($this->auth0_admin, 'render_settings_page') );
        add_submenu_page($main_menu, __('Users export', WPA0_LANG), __('Users export', WPA0_LANG), 'manage_options', 'wpa0-users-export', array($this->users_exporter, 'render_export_users') );
        add_submenu_page($main_menu, __('Dashboard preferences', WPA0_LANG), __('Dashboard', WPA0_LANG), 'manage_options', 'wpa0-dashboard', array($this->dashboard_preferences, 'render_dashboard_preferences_page') );
        add_submenu_page($main_menu, __('Error Log', WPA0_LANG), __('Error Log', WPA0_LANG), 'manage_options', 'wpa0-errors', array($this->error_log, 'render_settings_page') );

        if (WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled())
        {
            add_submenu_page($main_menu, __('JWT Auth integration', WPA0_LANG), __('JWT Auth integration', WPA0_LANG), 'manage_options', 'wpa0-jwt-auth', array($this->configure_jwt_auth, 'render_settings_page') );
        }
    }
}
