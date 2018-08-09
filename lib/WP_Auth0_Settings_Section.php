<?php

class WP_Auth0_Settings_Section {

	protected $a0_options;
	protected $initial_setup;
	protected $users_exporter;
	protected $configure_jwt_auth;
	protected $error_log;
	protected $auth0_admin;
	protected $import_settings;

	public function __construct( WP_Auth0_Options $a0_options, WP_Auth0_InitialSetup $initial_setup, WP_Auth0_Export_Users $users_exporter, WP_Auth0_Configure_JWTAUTH $configure_jwt_auth, WP_Auth0_ErrorLog $error_log, WP_Auth0_Admin $auth0_admin, WP_Auth0_Import_Settings $import_settings ) {
		$this->a0_options         = $a0_options;
		$this->initial_setup      = $initial_setup;
		$this->users_exporter     = $users_exporter;
		$this->configure_jwt_auth = $configure_jwt_auth;
		$this->error_log          = $error_log;
		$this->auth0_admin        = $auth0_admin;
		$this->import_settings    = $import_settings;
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'init_menu' ), 95.55, 0 );
	}

	public function init_menu() {

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'wpa0-help' ) {
			wp_redirect( admin_url( 'admin.php?page=wpa0#help' ), 301 );
			exit;
		}

		$main_menu = ! WP_Auth0::ready() ? 'wpa0-setup' : 'wpa0';

		add_menu_page(
			__( 'Auth0', 'wp-auth0' ),
			__( 'Auth0', 'wp-auth0' ),
			'manage_options',
			$main_menu,
			! WP_Auth0::ready() ?
				array( $this->initial_setup, 'render_setup_page' ) :
				array( $this->auth0_admin, 'render_settings_page' ),
			WPA0_PLUGIN_IMG_URL . 'a0icon.png',
			85.55
		);

		if ( ! WP_Auth0::ready() ) {
			add_submenu_page( $main_menu, __( 'Auth0 for WordPress - Setup Wizard', 'wp-auth0' ), __( 'Setup Wizard', 'wp-auth0' ), 'manage_options', 'wpa0-setup', array( $this->initial_setup, 'render_setup_page' ) );
			add_submenu_page( $main_menu, __( 'Settings', 'wp-auth0' ), __( 'Settings', 'wp-auth0' ), 'manage_options', 'wpa0', array( $this->auth0_admin, 'render_settings_page' ) );
		} else {
			add_submenu_page( $main_menu, __( 'Settings', 'wp-auth0' ), __( 'Settings', 'wp-auth0' ), 'manage_options', 'wpa0', array( $this->auth0_admin, 'render_settings_page' ) );

			add_submenu_page( $main_menu, __( 'Help', 'wp-auth0' ), __( 'Help', 'wp-auth0' ), 'manage_options', 'wpa0-help', array( $this, 'redirect_to_help' ) );

			add_submenu_page( $main_menu, __( 'Auth0 for WordPress - Setup Wizard', 'wp-auth0' ), __( 'Setup Wizard', 'wp-auth0' ), 'manage_options', 'wpa0-setup', array( $this->initial_setup, 'render_setup_page' ) );
		}

		add_submenu_page( $main_menu, __( 'Export Users Data', 'wp-auth0' ), __( 'Export Users Data', 'wp-auth0' ), 'manage_options', 'wpa0-users-export', array( $this->users_exporter, 'render_export_users' ) );
		add_submenu_page( $main_menu, __( 'Error Log', 'wp-auth0' ), __( 'Error Log', 'wp-auth0' ), 'manage_options', 'wpa0-errors', array( $this->error_log, 'render_settings_page' ) );
		add_submenu_page( $main_menu, __( 'Import-Export settings', 'wp-auth0' ), __( 'Import-Export settings', 'wp-auth0' ), 'manage_options', 'wpa0-import-settings', array( $this->import_settings, 'render_import_settings_page' ) );

		if ( WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled() ) {
			add_submenu_page( $main_menu, __( 'JWT Auth integration', 'wp-auth0' ), __( 'JWT Auth integration', 'wp-auth0' ), 'manage_options', 'wpa0-jwt-auth', array( $this->configure_jwt_auth, 'render_settings_page' ) );
		}
	}

	// TODO: deprecate
	public function redirect_to_help() {

	}
}
