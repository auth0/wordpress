<?php

class WP_Auth0_DBManager {

	protected $current_db_version = null;
	protected $a0_options;

	public function __construct($a0_options) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		$this->current_db_version = (int)get_option( 'auth0_db_version', 0 );
		if ($this->current_db_version === 0) {
			$this->current_db_version = (int)get_site_option( 'auth0_db_version', 0 );
		}
		add_action( 'plugins_loaded', array( $this, 'check_update' ) );
	}

	public function check_update() {
		if ( $this->current_db_version !== AUTH0_DB_VERSION ) {
			$this->install_db();
		}
	}

	public function install_db() {

		$options = WP_Auth0_Options::Instance();

		if ($this->current_db_version === 0) {
			$options->set('auth0_table', false);
		} elseif($options->get('auth0_table') === null) {
			$options->set('auth0_table', true);
		}

		$cdn_url = $options->get( 'cdn_url' );

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

		if ( $this->current_db_version < 9 ) {
			$this->migrate_users_data();
		}

		if ( $this->current_db_version < 10 ) {

			if ($options->get('use_lock_10') === null) {

				if ( strpos( $cdn_url, '10.' ) === false ) {
					$options->set('use_lock_10', false);
				} else {
					$options->set('use_lock_10', true);
				}

			}

			$dict = $options->get('dict');

			if (!empty($dict))
			{

				if (json_decode($dict) === null)
				{
					$options->set('language', $dict);
				}
				else
				{
					$options->set('language_dictionary', $dict);
				}

			}

		}

		if ( $this->current_db_version < 12 ) {

      if ( strpos( $cdn_url, '10.' ) === false ) {
        $options->set('use_lock_10', false);
      } else {
        $options->set('use_lock_10', true);
      }

    }

    if ( $this->current_db_version < 13 ) {
      $ips = $options->get('migration_ips');
      $oldips = '138.91.154.99,54.221.228.15,54.183.64.135,54.67.77.38,54.67.15.170,54.183.204.205,54.173.21.107,54.85.173.28';

      $ipCheck = new WP_Auth0_Ip_Check($options);

      if ( $ips === $oldips ) {
        $options->set('migration_ips', $ipCheck->get_ip_by_region('us'));
      }
    }

	if ( $this->current_db_version < 14 && is_null($options->get('client_secret_b64_encoded' ))) {
		if ( $options->get('client_id' )) {
			$options->set('client_secret_b64_encoded', true);
		} else {
			$options->set('client_secret_b64_encoded', false);
		}
	}

	if ( $this->current_db_version < 15 ) {
		$options->set('use_lock_10', true);
		$options->set('cdn_url', '//cdn.auth0.com/js/lock/11.1/lock.min.js');
		$options->set('auth0js-cdn', '//cdn.auth0.com/js/auth0/9.1/auth0.min.js');
		$options->set('cache_expiration', 1440);

		// Update Client
		$client_id = $options->get( 'client_id' );
		$domain = $options->get( 'domain' );
		if (!empty($client_id) && !empty($domain)) {
			$app_token = $options->get( 'auth0_app_token' );
			$sso = $options->get( 'sso' );
			$payload = array(
				"cross_origin_auth" => true,
				"cross_origin_loc" => site_url('index.php?auth0fallback=1','https'),
				"web_origins" => ( home_url() === site_url() ? array( home_url() ) : array( home_url(), site_url() ) )
			);
			$updateClient = WP_Auth0_Api_Client::update_client($domain, $app_token, $client_id, $sso, $payload);
			$options->set('client_signing_algorithm', 'HS256');
		}
	}
		$this->current_db_version = AUTH0_DB_VERSION;
		update_option( 'auth0_db_version', AUTH0_DB_VERSION );
	}

	protected function migrate_users_data() {
		global $wpdb;

		$wpdb->auth0_user = $wpdb->prefix.'auth0_user';

		$sql = 'SELECT a.*
				FROM ' . $wpdb->auth0_user .' a
				JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id;';

		$userRows = $wpdb->get_results( $sql );

		if ( is_null( $userRows ) ) {
			return;
		} elseif ( $userRows instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'migrate_users_data', $userRows );
			return;
		}

		$repo = new WP_Auth0_UsersRepo( $this->a0_options );

		foreach ($userRows as $row) {
			$auth0_id = get_user_meta( $row->wp_id, $wpdb->prefix.'auth0_id', true);

			if (!$auth0_id) {
				$repo->update_auth0_object( $row->wp_id, WP_Auth0_Serializer::unserialize($row->auth0_obj) );
			}
		}
	}

	public function get_auth0_users( $user_ids = null ) {
		global $wpdb;

		if ($user_ids === null) {
			$query = array( 'meta_key' => $wpdb->prefix.'auth0_id' );
		}
		else {
			$query = array( 'meta_query' => array(
        'key' => $wpdb->prefix.'auth0_id',
        'value' => $user_ids,
        'compare' => 'IN',
			) );
		}
		$query['blog_id'] = 0;

		$results = get_users( $query );

		if ( $results instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( 'findAuth0User', $userRow );
			return array();
		}

		return $results;
	}
}
