<?php

class WP_Auth0_Dashboard_Plugins  {

	public static function init() {
		add_action( 'wp_dashboard_setup', array(__CLASS__, 'set_up') );
	}

	public static function set_up() {

		wp_enqueue_style( 'auth0-dashboard-c3-css', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/lib/c3/c3.min.css' );
		wp_enqueue_script( 'auth0-dashboard-d3', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/lib/d3/d3.min.js' );
		wp_enqueue_script( 'auth0-dashboard-c3-js', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/lib/c3/c3.min.js' );

		$users = self::getUsers();
		$usersObjs = array_map(array(__CLASS__, 'getA0UserObj'), $users);

		$widgets = array(
			new WP_Auth0_Dashboard_Plugins_Age($usersObjs),
			new WP_Auth0_Dashboard_Plugins_Gender($usersObjs),
			new WP_Auth0_Dashboard_Plugins_IdP($usersObjs),
			new WP_Auth0_Dashboard_Plugins_Location($usersObjs),
		);

		foreach ($widgets as $widget) {
			wp_add_dashboard_widget( $widget->getId(), $widget->getName(), array($widget, 'render') );	
		}
	}

	public static function getA0UserObj($userArr) {
		return unserialize($userArr->auth0_obj);
	}

	protected static function getUsers() {
		global $wpdb;
        $sql = sprintf('SELECT * FROM %s a JOIN %s u ON a.wp_id = u.id', $wpdb->auth0_user, $wpdb->users);
        $results = $wpdb->get_results($sql);

        if($results instanceof WP_Error ) {
            self::insertAuth0Error('findAuth0User',$userRow);
            return array();
        }

        return $results;
	}

}