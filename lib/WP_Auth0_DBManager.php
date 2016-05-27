<?php

class WP_Auth0_DBManager {

	protected $current_db_version = null;
	protected $a0_options;

	public function __construct($a0_options) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		$this->current_db_version = (int)get_site_option( 'auth0_db_version' );
		add_action( 'plugins_loaded', array( $this, 'initialize_wpdb_tables' ) );
		add_action( 'plugins_loaded', array( $this, 'check_update' ) );
	}

	public function initialize_wpdb_tables() {
		global $wpdb;
		$wpdb->auth0_user = $wpdb->prefix.'auth0_user';
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

		if (!post_type_exists('auth0_error_log')) {
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
		update_site_option( 'auth0_db_version', AUTH0_DB_VERSION );
	}

	public function get_auth0_users( $user_ids = null ) {
		$results = get_users( array( 'meta_key' => 'auth0_id' ) );

		if ( $results instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'findAuth0User', $userRow );
			return array();
		}

		return $results;
	}
}
