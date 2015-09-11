<?php

class WP_Auth0_Dashboard_Widgets  {

	protected $db_manager;
	protected $dashboard_options;

	public function __construct(WP_Auth0_Dashboard_Options $dashboard_options, WP_Auth0_DBManager $db_manager) {
		$this->db_manager = $db_manager;
		$this->dashboard_options = $dashboard_options;
	}

	public function init() {
		add_action( 'wp_dashboard_setup', array( $this, 'set_up' ) );
	}

	public function set_up() {
		global $current_user;

		if ( ! in_array( 'administrator', $current_user->roles ) ) {
			return;
		}

		wp_enqueue_style( 'auth0-dashboard-c3-css', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/c3/c3.min.css' );
		wp_enqueue_style( 'auth0-dashboard-css', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/css/dashboard.css' );

		wp_enqueue_script( 'auth0-dashboard-d3', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/d3/d3.min.js' );
		wp_enqueue_script( 'auth0-dashboard-c3-js', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/c3/c3.min.js' );

		$users = $this->db_manager->get_auth0_users();

		$widgets = array(
			new WP_Auth0_Dashboard_Plugins_Age($this->dashboard_options),
			new WP_Auth0_Dashboard_Plugins_Gender($this->dashboard_options),
			new WP_Auth0_Dashboard_Plugins_IdP($this->dashboard_options),
			new WP_Auth0_Dashboard_Plugins_Location(),
			new WP_Auth0_Dashboard_Plugins_Income(),
			new WP_Auth0_Dashboard_Plugins_Signups(),
		);

		foreach ($users as $user) {
			$userObj = new WP_Auth0_UserProfile($user->auth0_obj);
			foreach ($widgets as $widget) {
				$widget->addUser($userObj);
			}
		}

		foreach ( $widgets as $widget ) {
			wp_add_dashboard_widget( $widget->getId(), $widget->getName(), array( $widget, 'render' ) );
		}
	}

}
