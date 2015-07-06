<?php

class WP_Auth0_DBManager {

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'initialize_wpdb_tables' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'check_update' ) );
	}

	public static function initialize_wpdb_tables() {
		global $wpdb;

		$wpdb->auth0_log = $wpdb->prefix.'auth0_log';
		$wpdb->auth0_user = $wpdb->prefix.'auth0_user';
		$wpdb->auth0_error_logs = $wpdb->prefix.'auth0_error_logs';
	}

	public static function check_update() {
		if ( (int) get_site_option( 'auth0_db_version' ) !== AUTH0_DB_VERSION ) {
			self::install_db();
		}
	}

	public static function install_db() {
		global $wpdb;

		self::initialize_wpdb_tables();

		$sql = array();

		$sql[] = "CREATE TABLE ".$wpdb->auth0_log." (
					id INT(11) AUTO_INCREMENT NOT NULL,
					event VARCHAR(100) NOT NULL,
					level VARCHAR(100) NOT NULL DEFAULT 'notice',
					description TEXT,
					details LONGTEXT,
					logtime INT(11) NOT NULL,
					PRIMARY KEY  (id)
				);";

		$sql[] = "CREATE TABLE ".$wpdb->auth0_user." (
					auth0_id VARCHAR(100) NOT NULL,
					wp_id INT(11)  NOT NULL,
					auth0_obj TEXT,
					PRIMARY KEY  (auth0_id)
				);";

		$sql[] = "CREATE TABLE ".$wpdb->auth0_error_logs." (
					id INT(11) AUTO_INCREMENT NOT NULL,
					date DATETIME  NOT NULL,
					section VARCHAR(255),
					code VARCHAR(255),
					message TEXT,
					PRIMARY KEY  (id)
				);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach ( $sql as $s ) {
			dbDelta( $s );
		}
		update_option( 'auth0_db_version', AUTH0_DB_VERSION );

		$cdn_url = WP_Auth0_Options::get( 'cdn_url' );
		if ( strpos( $cdn_url, 'auth0-widget-5' ) !== false || strpos( $cdn_url, 'lock-6' ) !== false ) {
			WP_Auth0_Options::set( 'cdn_url', '//cdn.auth0.com/js/lock-7.min.js' );
		}

	}
}
