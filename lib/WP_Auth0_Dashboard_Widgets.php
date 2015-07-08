<?php

class WP_Auth0_Dashboard_Widgets  {

	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'set_up' ) );
	}

	public static function set_up() {
		global $current_user;

		if ( ! in_array( 'administrator', $current_user->roles ) ) {
			return;
		}

		wp_enqueue_style( 'auth0-dashboard-c3-css', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/c3/c3.min.css' );
		wp_enqueue_style( 'auth0-dashboard-css', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/css/dashboard.css' );

		wp_enqueue_script( 'auth0-dashboard-d3', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/d3/d3.min.js' );
		wp_enqueue_script( 'auth0-dashboard-c3-js', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/c3/c3.min.js' );

		$users = self::get_users();

		$options = WP_Auth0_Dashboard_Options::Instance();

		$widgets = array(
			new WP_Auth0_Dashboard_Plugins_Age($options->get('chart_age_type')),
			new WP_Auth0_Dashboard_Plugins_Gender($options->get('chart_gender_type')),
			new WP_Auth0_Dashboard_Plugins_IdP($options->get('chart_idp_type')),
			new WP_Auth0_Dashboard_Plugins_Location(),
			new WP_Auth0_Dashboard_Plugins_Income(),
			new WP_Auth0_Dashboard_Plugins_Signups(),
		);

		foreach ($users as $user) {
			$userObj = self::get_a0_user_obj($user);
			foreach ($widgets as $widget) {
				$widget->addUser($userObj);
			}
		}

		foreach ( $widgets as $widget ) {
			wp_add_dashboard_widget( $widget->getId(), $widget->getName(), array( $widget, 'render' ) );
		}
	}

	public static function get_a0_user_obj( $userArr ) {
		return unserialize( $userArr->auth0_obj );
	}

	protected static function get_users() {
		global $wpdb;
		$sql = sprintf( 'SELECT a.* FROM %s a JOIN %s u ON a.wp_id = u.id', $wpdb->auth0_user, $wpdb->users );
		$results = $wpdb->get_results( $sql );

		if ( $results instanceof WP_Error ) {
			self::insert_auth0_error( 'findAuth0User',$userRow );
			return array();
		}

		return $results;
	}

}
