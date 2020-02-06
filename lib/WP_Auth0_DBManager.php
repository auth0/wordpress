<?php

class WP_Auth0_DBManager {

	protected $current_db_version = null;
	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function install_db( $version_to_install = null ) {

		$current_ver = (int) get_option( 'auth0_db_version', 0 );
		if ( $current_ver === 0 ) {
			$current_ver = (int) get_site_option( 'auth0_db_version', 0 );
		}

		if ( empty( $current_ver ) || $current_ver === AUTH0_DB_VERSION ) {
			update_option( 'auth0_db_version', AUTH0_DB_VERSION );
			return;
		}

		wp_cache_set( 'doing_db_update', true, WPA0_CACHE_GROUP );

		$options = $this->a0_options;

		// Plugin version < 3.4.0
		if ( $current_ver < 15 || 15 === $version_to_install ) {
			$options->set( 'cdn_url', WPA0_LOCK_CDN_URL, false );
			$options->set( 'cache_expiration', 1440, false );

			// Update Client
			if ( wp_auth0_is_ready() ) {
				$options->set( 'client_signing_algorithm', 'HS256', false );
			}
		}

		// Plugin version < 3.5.0
		if ( $current_ver < 16 || 16 === $version_to_install ) {

			// Update Lock and Auth versions
			if ( '//cdn.auth0.com/js/lock/11.0.0/lock.min.js' === $options->get( 'cdn_url' ) ) {
				$options->set( 'cdn_url', WPA0_LOCK_CDN_URL, false );
			}
		}

		// Plugin version < 3.6.0
		if ( $current_ver < 18 || 18 === $version_to_install ) {

			// Migrate passwordless_method
			if ( $options->get( 'passwordless_enabled', false ) ) {
				$pwl_method = $options->get( 'passwordless_method' );
				switch ( $pwl_method ) {

					// SMS passwordless just needs 'sms' as a connection
					case 'sms':
						$options->set( 'lock_connections', 'sms', false );
						break;

					// Social + SMS means there are existing social connections we want to keep
					case 'socialOrSms':
						$options->add_lock_connection( 'sms' );
						break;

					// Email link passwordless just needs 'email' as a connection
					case 'emailcode':
					case 'magiclink':
						$options->set( 'lock_connections', 'email', false );
						break;

					// Social + Email means there are social connections be want to keep
					case 'socialOrMagiclink':
					case 'socialOrEmailcode':
						$options->add_lock_connection( 'email' );
						break;
				}

				// Need to set a special passwordlessMethod flag if using email code
				$lock_json                               = trim( $options->get( 'extra_conf' ) );
				$lock_json_decoded                       = ! empty( $lock_json ) ? json_decode( $lock_json, true ) : [];
				$lock_json_decoded['passwordlessMethod'] = strpos( $pwl_method, 'code' ) ? 'code' : 'link';
				$options->set( 'extra_conf', json_encode( $lock_json_decoded ), false );
			}

			$options->remove( 'passwordless_method' );
		}

		// 3.9.0
		if ( $current_ver < 20 || 20 === $version_to_install ) {

			// Remove default IP addresses from saved field.
			$migration_ips = trim( $options->get( 'migration_ips' ) );
			if ( $migration_ips ) {
				$migration_ips = array_map( 'trim', explode( ',', $migration_ips ) );
				$ip_check      = new WP_Auth0_Ip_Check( $options );
				$default_ips   = explode( ',', $ip_check->get_ips_by_domain() );
				$custom_ips    = array_diff( $migration_ips, $default_ips );
				$options->set( 'migration_ips', implode( ',', $custom_ips ), false );
			}
		}

		// 3.10.0
		if ( $current_ver < 21 || 21 === $version_to_install ) {

			if ( 'https://cdn.auth0.com/js/lock/11.5/lock.min.js' === $options->get( 'cdn_url' ) ) {
				$options->set( 'cdn_url', WPA0_LOCK_CDN_URL, false );
				$options->set( 'custom_cdn_url', false, false );
			} else {
				$options->set( 'custom_cdn_url', true, false );
			}

			// Nullify and delete all removed options.
			$options->remove( 'auth0js-cdn' );
			$options->remove( 'passwordless_cdn_url' );
			$options->remove( 'cdn_url_legacy' );

			$options->remove( 'social_twitter_key' );
			$options->remove( 'social_twitter_secret' );
			$options->remove( 'social_facebook_key' );
			$options->remove( 'social_facebook_secret' );
			$options->remove( 'connections' );

			$options->remove( 'chart_idp_type' );
			$options->remove( 'chart_gender_type' );
			$options->remove( 'chart_age_type' );
			$options->remove( 'chart_age_from' );
			$options->remove( 'chart_age_to' );
			$options->remove( 'chart_age_step' );

			// Migrate WLE setting
			$new_wle_value = $options->get( 'wordpress_login_enabled' ) ? 'link' : 'isset';
			$options->set( 'wordpress_login_enabled', $new_wle_value, false );
			$options->set( 'wle_code', str_shuffle( uniqid() . uniqid() ), false );

			// Remove Client Grant update notifications.
			delete_option( 'wp_auth0_client_grant_failed' );
			delete_option( 'wp_auth0_grant_types_failed' );
			delete_option( 'wp_auth0_client_grant_success' );
			delete_option( 'wp_auth0_grant_types_success' );
		}

		// 3.11.0
		if ( $current_ver < 22 || 22 === $version_to_install ) {
			$options->remove( 'social_big_buttons' );
		}

		// 4.0.0
		if ( $current_ver < 23 || 23 === $version_to_install ) {
			$extra_conf = json_decode( $options->get( 'extra_conf' ), true );
			if ( empty( $extra_conf ) ) {
				$extra_conf = [];
			}

			$language = $options->get( 'language' );
			if ( $language ) {
				$extra_conf['language'] = $language;
			}
			$options->remove( 'language' );

			$language_dict = json_decode( $options->get( 'language_dictionary' ), true );
			if ( $language_dict ) {
				$extra_conf['languageDictionary'] = $language_dict;
			}
			$options->remove( 'language_dictionary' );

			if ( ! empty( $extra_conf ) ) {
				$options->set( 'extra_conf', wp_json_encode( $extra_conf ) );
			}

			$options->remove( 'jwt_auth_integration' );
			$options->remove( 'link_auth0_users' );
			$options->remove( 'custom_css' );
			$options->remove( 'custom_js' );
			$options->remove( 'auth0_implicit_workflow' );
			$options->remove( 'client_secret_b64_encoded' );
			$options->remove( 'custom_signup_fields' );
			$options->remove( 'migration_token_id' );
		}

		$options->update_all();
		update_option( 'auth0_db_version', AUTH0_DB_VERSION );
		wp_cache_set( 'doing_db_update', false, WPA0_CACHE_GROUP );
	}
}
