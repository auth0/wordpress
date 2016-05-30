<?php

class WP_Auth0_DBManager {

	protected $current_db_version = null;
	protected $a0_options;

	public function __construct($a0_options) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		$this->current_db_version = (int)get_site_option( 'auth0_db_version' );
		add_action( 'plugins_loaded', array( $this, 'check_update' ) );
	}

	public function register_custom_post_types() {
		if ( ! post_type_exists( 'auth0_error_log' ) ) {
			register_post_type( 'auth0_error_log',
		    array(
		      'labels' => array(
		        'name' => __( 'Auth0 Errors' ),
		        'singular_name' => __( 'Auth0 Error' )
		      ),
		      'public' => false,
		      'has_archive' => false,
		      'exclude_from_search' => true, 
		      'publicly_queryable' => false, 
		      'show_ui' => false, 
		      'show_in_nav_menus' => false, 
		      'show_in_menu' => false, 
		      'show_in_admin_bar' => false, 
		      'capability_type' => false, 
		      'query_var' => false, 
		      'show_in_rest' => false, 
		      'capabilities' => array(
				    'edit_post'          => 'update_core',
				    'read_post'          => 'update_core',
				    'delete_post'        => 'update_core',
				    'edit_posts'         => 'update_core',
				    'edit_others_posts'  => 'update_core',
				    'delete_posts'       => 'update_core',
				    'publish_posts'      => 'update_core',
				    'read_private_posts' => 'update_core'
					),
		    )
		  );
		}
	}

	public function check_update() {
		if ( $this->current_db_version !== AUTH0_DB_VERSION ) {
			$this->install_db();
		}
	}

	public function install_db() {

		if ($this->current_db_version === 0) {
			$this->a0_options->set('auth0_table', false);
		} elseif($this->a0_options->get('auth0_table') === null) {
			$this->a0_options->set('auth0_table', true);
		}

		$options = WP_Auth0_Options::Instance();
		$cdn_url = $options->get( 'cdn_url' );
		if ( strpos( $cdn_url, 'auth0-widget-5' ) !== false || strpos( $cdn_url, 'lock-6' ) !== false ) {
			$options->set( 'cdn_url', '//cdn.auth0.com/js/lock-9.1.min.js' );
		}
		if ( strpos( $cdn_url, 'auth0-widget-5' ) !== false || strpos( $cdn_url, 'lock-8' ) !== false ) {
			$options->set( 'cdn_url', '//cdn.auth0.com/js/lock-9.1.min.js' );
		}
		if ( strpos( $cdn_url, 'auth0-widget-5' ) !== false || strpos( $cdn_url, 'lock-9.0' ) !== false ) {
			$options->set( 'cdn_url', '//cdn.auth0.com/js/lock-9.1.min.js' );
		}

		if ( $this->current_db_version < 8 ) {
			$this->migrate_users_data();
		}

		if ( $this->current_db_version <= 7 ) {
			if ( $options->get( 'db_connection_enabled' ) ) {

				$app_token = $options->get( 'auth0_app_token' );
				$connection_id = $options->get( 'db_connection_id' );
				$migration_token = $options->get( "migration_token" );

				$operations = new WP_Auth0_Api_Operations( $options );
				if ( !empty( $app_token ) &&
					!empty( $connection_id ) &&
					!empty( $migration_token ) ) {

					$response = $operations->update_wordpress_connection(
						$app_token,
						$connection_id,
						$options->get( 'password_policy' ),
						$migration_token );
				}
			}
		}

		$this->current_db_version = AUTH0_DB_VERSION;
		// update_site_option( 'auth0_db_version', AUTH0_DB_VERSION );
	}

	protected function migrate_users_data() {
		global $wpdb;

		$wpdb->auth0_user = $wpdb->prefix.'auth0_user';

		$sql = 'SELECT a.*
				FROM ' . $wpdb->auth0_user .' a
				JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
				ORDER BY a.last_update DESC;';

		$userRows = $wpdb->get_row( $sql );
var_dump($userRows);exit;
		if ( is_null( $userRows ) ) {
			return;
		} elseif ( $userRows instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'migrate_users_data', $userRows );
			return;
		}

		$repo = new WP_Auth0_UsersRepo( $this->a0_options );

		foreach ($userRows as $row) {
			$auth0_id = get_user_meta( $current_user, 'auth0_id', true);

			if (!$auth0_id) {
				$repo->update_auth0_object( $row->wp_id, WP_Auth0_Serializer::unserialize($row->auth0_obj) );
			}
		}

	}

	public function get_auth0_users( $user_ids = null ) {

		if ($user_ids === null) {
			$query = array( 'meta_key' => 'auth0_id' );
		}
		else {
			$query = array( 'meta_query' => array(
        'key' => 'auth0_id',
        'value' => $user_ids,
        'compare' => 'IN',
			) );
		}

		$results = get_users( $query );

		if ( $results instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'findAuth0User', $userRow );
			return array();
		}

		return $results;
	}
}
