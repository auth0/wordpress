<?php

class WP_Auth0_DBManager {

	public function init() {
		add_action( 'plugins_loaded', array( $this, 'initialize_wpdb_tables' ) );
		add_action( 'plugins_loaded', array( $this, 'check_update' ) );
	}

	public function initialize_wpdb_tables() {
		global $wpdb;

		$wpdb->auth0_log = $wpdb->prefix.'auth0_log';
		$wpdb->auth0_user = $wpdb->prefix.'auth0_user';
		$wpdb->auth0_error_logs = $wpdb->prefix.'auth0_error_logs';
	}

	public function check_update() {
		if ( (int) get_site_option( 'auth0_db_version' ) !== AUTH0_DB_VERSION ) {
			$this->install_db();
		}
	}

	public function install_db() {
		global $wpdb;

		$this->initialize_wpdb_tables();

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
					last_update DATETIME,
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
		update_site_option( 'auth0_db_version', AUTH0_DB_VERSION );

		$options = WP_Auth0_Options::Instance();
		$cdn_url = $options->get( 'cdn_url' );
		if ( strpos( $cdn_url, 'auth0-widget-5' ) !== false || strpos( $cdn_url, 'lock-6' ) !== false ) {
			$options->set( 'cdn_url', '//cdn.auth0.com/js/lock-9.0.min.js' );
		}
		if ( strpos( $cdn_url, 'auth0-widget-5' ) !== false || strpos( $cdn_url, 'lock-8' ) !== false ) {
			$options->set( 'cdn_url', '//cdn.auth0.com/js/lock-9.0.min.js' );
		}

	}

	public function get_auth0_users($user_ids = null) {
		global $wpdb;

		$where = '';
		if ( $user_ids ) {
			$ids = esc_sql( implode( ',',  array_filter( $user_ids, 'ctype_digit' ) ) );
			$where .= " AND u.id IN ($ids) ";
		}

		$sql = sprintf( 'SELECT a.* FROM %s a JOIN %s u ON a.wp_id = u.id %s', $wpdb->auth0_user, $wpdb->users, $where );
		$results = $wpdb->get_results( $sql );

		if ( $results instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'findAuth0User',$userRow );
			return array();
		}

		return $results;
	}

	public function get_users_by_auth0id($auth0_id) {
		global $wpdb;


		$sql = sprintf( 'SELECT a.* FROM %s a JOIN %s u ON u.wp_id = a.id where u.auth0_id = "%s"',
			$wpdb->users,
			$wpdb->auth0_user,
			$auth0_id );

		$result = $wpdb->get_row( $sql );

		if ( $result instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'findAuth0User',$userRow );
			return null;
		}

		return $result;
	}

	function get_currentauth0userinfo() {

		global $currentauth0_user;

		$result = $this->get_currentauth0user();
		if ($result) {
			$currentauth0_user = WP_Auth0_Serializer::unserialize( $result->auth0_obj );
		}

		return $currentauth0_user;
	}

	function get_currentauth0user() {
		global $current_user;
		global $wpdb;

		wp_get_current_user();

		if ( $current_user instanceof WP_User && $current_user->ID > 0 ) {
			$sql = 'SELECT * FROM ' . $wpdb->auth0_user .' WHERE wp_id = %d order by last_update desc limit 1';
			$result = $wpdb->get_row( $wpdb->prepare( $sql, $current_user->ID ) );

			if ( is_null( $result ) || $result instanceof WP_Error ) {
				return null;
			}

		}

		return $result;
	}

	public function get_current_user_profiles() {
        global $current_user;
        global $wpdb;

        wp_get_current_user();
        $userData = array();

        if ($current_user instanceof WP_User && $current_user->ID > 0 ) {
            $sql = 'SELECT auth0_obj
                    FROM ' . $wpdb->auth0_user .'
                    WHERE wp_id = %d';
            $results = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));

            if (is_null($results) || $results instanceof WP_Error ) {

                return null;
            }

            foreach ($results as $value) {
                $userData[] = WP_Auth0_Serializer::unserialize($value->auth0_obj);
            }

        }

        return $userData;
    }
}
