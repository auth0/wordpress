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
		add_action( 'admin_notices', array( $this, 'notice_failed_client_grant' ) );
		add_action( 'admin_notices', array( $this, 'notice_successful_client_grant' ) );
		add_action( 'admin_notices', array( $this, 'notice_successful_grant_types' ) );
	}

	public function check_update() {
		if ( $this->current_db_version && $this->current_db_version !== AUTH0_DB_VERSION ) {
			$this->install_db();
		}
	}

	public function install_db( $version_to_install = null, $app_token = '' ) {

		wp_cache_set( 'doing_db_update', TRUE, WPA0_CACHE_GROUP );

		$options = WP_Auth0_Options::Instance();

		if ( empty( $app_token ) ) {
			$app_token = $options->get( 'auth0_app_token' );
		}

		$connection_id = $options->get( 'db_connection_id' );
		$migration_token = $options->get( 'migration_token' );
		$client_id = $options->get( 'client_id' );
		$client_secret = $options->get( 'client_secret' );
		$domain = $options->get( 'domain' );
		$sso = $options->get( 'sso' );
		$cdn_url = $options->get( 'cdn_url' );

		if ( $this->current_db_version <= 7 ) {
			if ( $options->get( 'db_connection_enabled' ) ) {

				$operations = new WP_Auth0_Api_Operations( $options );

				if ( !empty( $app_token ) &&
					!empty( $connection_id ) &&
					!empty( $migration_token ) ) {

					$operations->update_wordpress_connection(
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
		
		// 3.4.0

		if ( $this->current_db_version < 15 || 15 === $version_to_install  ) {
			$options->set('cdn_url', WPA0_LOCK_CDN_URL);
			$options->set('auth0js-cdn', WPA0_AUTH0_JS_CDN_URL);
			$options->set('cache_expiration', 1440);

			// Update Client
			if (WP_Auth0::ready()) {
				$options->set('client_signing_algorithm', 'HS256');
			}
		}

		// App token needed for following updates

		$decoded_token = null;
		if ( ! empty( $app_token ) ) {

			$token_parts = explode( '.', $app_token );

			try {
				$header = json_decode( JWT::urlsafeB64Decode( $token_parts[0] ) );
				$decoded_token = JWT::decode(
					$app_token,
					$options->convert_client_secret_to_key( $client_secret, FALSE, 'RS256' === $header->alg, $domain ),
					array( $header->alg )
				);
			} catch ( Exception $e ) {
				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $e->getMessage() );
			}
		}

		// 3.5.0

		if ( ( $this->current_db_version < 16 && 0 !== $this->current_db_version ) || 16 === $version_to_install ) {

			// Update Lock and Auth versions

			if ( '//cdn.auth0.com/js/lock/11.0.0/lock.min.js' === $options->get( 'cdn_url' ) ) {
				$options->set( 'cdn_url', WPA0_LOCK_CDN_URL );
			}

			if ( '//cdn.auth0.com/js/auth0/9.0.0/auth0.min.js' === $options->get( 'auth0js-cdn' ) ) {
				$options->set( 'auth0js-cdn', WPA0_AUTH0_JS_CDN_URL );
			}

			// Update app type and client grant

			$client_grant_created = FALSE;
			if ( $decoded_token ) {

				$payload = array(
					'app_type' => 'regular_web',
				);

				// Update the WP-created client
				$client_updated = WP_Auth0_Api_Client::update_client( $domain, $app_token, $client_id, $sso, $payload );

				// Create the client grant to the management API for the WP app client
				if ( $client_updated ) {
					$client_grant_created = WP_Auth0_Api_Client::create_client_grant( $app_token, $client_id );
				}
			}

			if ( $client_grant_created ) {
				delete_option( 'wp_auth0_client_grant_failed' );
				update_option( 'wp_auth0_client_grant_success', 1 );

				if ( 409 !== $client_grant_created->statusCode ) {
					WP_Auth0_ErrorManager::insert_auth0_error(
						__METHOD__,
						'Client Grant has been successfully created!'
					);
				}
			} else {
				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, sprintf(
					__( 'Unable to automatically create Client Grant. Please go to your Auth0 Dashboard '
					    . 'and authorize your Application %s for management API scopes %s.',
						'wp-auth0' ),
					$options->get( 'client_id' ),
					implode( ', ', WP_Auth0_Api_Client::get_required_scopes() )
				) );
				update_option( 'wp_auth0_client_grant_failed', 1 );
			}
		}

		// 3.5.1

		if ( ( $this->current_db_version < 17 && 0 !== $this->current_db_version ) || 17 === $version_to_install ) {

			$grant_types_updated = FALSE;
			$payload = array();

			// Need a valid app token to update audience and client grant
			if ( $decoded_token ) {

				$get_client_resp = WP_Auth0_Api_Client::get_client( $app_token, $client_id );

				if ( $get_client_resp ) {

					if ( is_array( $get_client_resp->grant_types ) ) {

						if ( FALSE === array_search( 'client_credentials', $get_client_resp->grant_types ) ) {
							$payload[ 'grant_types' ] = $get_client_resp->grant_types;
							$payload[ 'grant_types' ][] = 'client_credentials';
						} else {
							$grant_types_updated = TRUE;
						}

					} else {

						$payload[ 'grant_types' ] = WP_Auth0_Api_Client::get_client_grant_types();
					}

					if ( ! empty( $payload ) ) {
						$client_updated = WP_Auth0_Api_Client::update_client( $domain, $app_token, $client_id, $sso, $payload );
						$grant_types_updated = ! empty( $client_updated );
					}
				}
			}

			if ( $grant_types_updated ) {
				delete_option( 'wp_auth0_grant_types_failed' );
				update_option( 'wp_auth0_grant_types_success', 1 );
				WP_Auth0_ErrorManager::insert_auth0_error(
					__METHOD__,
					'Client Grant Types have been successfully updated!'
				);
			} else {
				WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, sprintf(
					__( 'Unable to automatically update Client Grant Type. Please go to your Auth0 Dashboard '
					    . 'and add Client Credentials to your Application settings > Advanced > Grant Types for ID %s ',
						'wp-auth0' ),
					$options->get( 'client_id' )
				) );
				update_option( 'wp_auth0_grant_types_failed', 1 );
			}
		}

		// 3.6.0

		if ( ( $this->current_db_version < 18 && 0 !== $this->current_db_version ) || 18 === $version_to_install ) {

			// Migrate passwordless_method
			if ( $options->get( 'passwordless_enabled', FALSE ) ) {
				$pwl_method = $options->get( 'passwordless_method' );
				switch ( $pwl_method ) {

					// SMS passwordless just needs 'sms' as a connection
					case 'sms' :
						$options->set( 'lock_connections', 'sms' );
						break;

					// Social + SMS means there are existing social connections we want to keep
					case 'socialOrSms' :
						$options->add_lock_connection( 'sms' );
						break;

					// Email link passwordless just needs 'email' as a connection
					case 'emailcode' :
					case 'magiclink' :
						$options->set( 'lock_connections', 'email' );
						break;

					// Social + Email means there are social connections be want to keep
					case 'socialOrMagiclink' :
					case 'socialOrEmailcode' :
						$options->add_lock_connection( 'email' );
						break;
				}

				// Need to set a special passwordlessMethod flag if using email code
				$lock_json = trim( $options->get( 'extra_conf' ) );
				$lock_json_decoded = ! empty( $lock_json ) ? json_decode( $lock_json, TRUE ) : array();
				$lock_json_decoded[ 'passwordlessMethod' ] = strpos( $pwl_method, 'code' ) ? 'code' : 'link';
				$options->set( 'extra_conf', json_encode( $lock_json_decoded ) );
			}

			// Set passwordless_cdn_url to latest Lock
			$options->set( 'passwordless_cdn_url', WPA0_LOCK_CDN_URL );

			// Force passwordless_method to delete
			$update_options = $options->get_options();
			unset( $update_options[ 'passwordless_method' ] );
			update_option( $options->get_options_name(), $update_options );
		}

		$this->current_db_version = AUTH0_DB_VERSION;
		update_option( 'auth0_db_version', AUTH0_DB_VERSION );

		wp_cache_set( 'doing_db_update', FALSE, WPA0_CACHE_GROUP );
	}

	/**
	 * Display a banner if we are not able to get a Management API token
	 * Hooked to admin_notices in $this->init()
	 */
	public function notice_failed_client_grant() {

		if (
			( get_option( 'wp_auth0_client_grant_failed' ) || get_option( 'wp_auth0_grant_types_failed' ) )
			&& current_user_can( 'update_plugins' )
		) {

			if ( WP_Auth0_Api_Client::get_client_token() ) {
				delete_option( 'wp_auth0_client_grant_failed' );
				delete_option( 'wp_auth0_grant_types_failed' );
			} else {
				?>
				<div class="notice notice-error">
					<p><strong><?php _e( 'IMPORTANT!', 'wp-auth0' ) ?></strong></p>
					<p><?php
						printf(
							__( 'WP-Auth0 has upgraded to %s but could not complete the upgrade in your Auth0 dashboard.', 'wp-auth0' ),
							WPA0_VERSION
						); ?>
						<?php _e( 'This can be fixed one of 2 ways:', 'wp-auth0' ) ?></p>
					<p><strong>1.</strong>
						<a href="https://auth0.com/docs/api/management/v2/tokens#get-a-token-manually" target="_blank"><?php
							_e( 'Create a new Management API token', 'wp-auth0' ) ?></a>
						<?php _e( 'and save it in the Auth0 > Settings > Basic tab > API Token field.', 'wp-auth0' ) ?>
						<?php _e( 'This will run the update process again.', 'wp-auth0' ) ?></p>
					<p><strong>2.</strong>
						<a href="https://auth0.com/docs/cms/wordpress/configuration#client-setup"
						   target="_blank"><?php
							_e( 'Review your Application advanced settings', 'wp-auth0' ) ?></a>,
						<?php _e( 'specifically the Grant Types, and ', 'wp-auth0' ) ?>
						<a href="https://auth0.com/docs/cms/wordpress/configuration#authorize-the-application-for-the-management-api"
						   target="_blank"><?php
							_e( 'authorize your client for the Management API', 'wp-auth0' ) ?></a>
						<?php _e( 'to manually complete the setup.', 'wp-auth0' ) ?>
					</p>
					<p><?php _e( 'This banner will disappear once the process is complete.', 'wp-auth0' ) ?></p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Display a banner once after 3.5.0 upgrade
	 * Hooked to admin_notices in $this->init()
	 */
	public function notice_successful_client_grant() {

		if ( ! get_option( 'wp_auth0_client_grant_success' )) {
			return;
		}
		?>
		<div class="notice notice-success">
			<p><?php
				_e( 'As a part of this upgrade, a Client Grant was created for the Auth0 Management API.', 'wp-auth0' );
				?><br><?php
				_e( 'Please check the plugin error log for any additional instructions to complete the upgrade.', 'wp-auth0' );
			?><br><a href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ) ?>">
					<strong><?php _e( 'Error Log', 'wp-auth0' ); ?></strong></a></p>
		</div>
		<?php
		delete_option( 'wp_auth0_client_grant_success' );
	}

	/**
	 * Display a banner once after 3.5.1 upgrade
	 * Hooked to admin_notices in $this->init()
	 */
	public function notice_successful_grant_types() {

		if ( ! get_option( 'wp_auth0_grant_types_success' ) ) {
			return;
		}
		?>
		<div class="notice notice-success">
			<p><?php
				_e( 'As a part of this upgrade, your Client Grant Types have been updated, if needed.', 'wp-auth0' ); ?></p>
		</div>
		<?php
		delete_option( 'wp_auth0_grant_types_success' );
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
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $userRows );
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
			WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $results->get_error_message() );
			return array();
		}

		return $results;
	}
}
