<?php

class WP_Auth0_Admin_Basic extends WP_Auth0_Admin_Generic {

	protected $description = 'Basic settings related to Auth0 credentials and basic WordPress integration';
	protected $actions_middlewares = array( 'basic_validation' );

	/**
	 * Sets up AJAX handler and settings field registration
	 */
	public function init() {
		add_action( 'wp_ajax_auth0_delete_cache_transient', array( $this, 'auth0_delete_cache_transient' ) );
		$this->init_option_section( '', 'basic', array(
				array( 'id' => 'wpa0_domain',
				       'name' => __( 'Domain', 'wp-auth0' ),
				       'function' => 'render_domain' ),
				array( 'id' => 'wpa0_client_id',
				       'name' => __( 'Client ID', 'wp-auth0' ),
				       'function' => 'render_client_id' ),
				array( 'id' => 'wpa0_client_secret',
				       'name' => __( 'Client Secret', 'wp-auth0' ),
				       'function' => 'render_client_secret' ),
				array( 'id' => 'wpa0_client_secret_b64_encoded',
				       'name' => __( 'Client Secret Base64 Encoded', 'wp-auth0' ),
				       'function' => 'render_client_secret_b64_encoded' ),
				array( 'id' => 'wpa0_client_signing_algorithm',
				       'name' => __( 'Client Signing Algorithm', 'wp-auth0' ),
				       'function' => 'render_client_signing_algorithm' ),
				array( 'id' => 'wpa0_cache_expiration',
				       'name' => __( 'Cache Time (in minutes)', 'wp-auth0' ),
				       'function' => 'render_cache_expiration' ),
				array( 'id' => 'wpa0_auth0_app_token',
				       'name' => __( 'API Token', 'wp-auth0' ),
				       'function' => 'render_auth0_app_token' ),
				array( 'id' => 'wpa0_login_enabled',
				       'name' => __( 'WordPress Login Enabled', 'wp-auth0' ),
				       'function' => 'render_allow_wordpress_login' ),
				array( 'id' => 'wpa0_allow_signup',
				       'name' => __( 'Allow Signups', 'wp-auth0' ),
				       'function' => 'render_allow_signup' ),
			) );
	}

	/**
	 * Render domain setting field
	 */
	public function render_domain() {
		$this->render_text_field( 'wpa0_domain', 'domain', 'text', 'your-tenant.auth0.com' );
		$this->render_field_description(
			__( 'Auth0 Domain, found in your Client settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' )
		);
	}

	/**
	 * Render client_id settings field
	 */
	public function render_client_id() {
		$this->render_text_field( 'wpa0_client_id', 'client_id' );
		$this->render_field_description(
			__( 'Client ID, found in your Client settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' )
		);
	}

	/**
	 * Render client_secret field (should never actually be displayed)
	 */
	public function render_client_secret() {
		$this->render_text_field( 'wpa0_client_secret', 'client_secret', 'password' );
		$this->render_field_description(
			__( 'Client Secret, found in your Client settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' )
		);
	}

	/**
	 * Render client_secret_b64_encoded
	 */
	public function render_client_secret_b64_encoded() {
		$this->render_switch( 'wpa_client_secret_b64_encoded', 'client_secret_b64_encoded' );
		$this->render_field_description(
			__( 'Enable if your client secret is base64 enabled; ', 'wp-auth0' ) .
			__( 'found below the Client Secret field in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' )
		);
	}

	/**
	 * Render client signing algorithm choices
	 */
	public function render_client_signing_algorithm() {
		$value = $this->options->get( 'client_signing_algorithm',  WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG );
		$this->render_radio_button( 'wpa0_client_signing_algorithm_hs', 'client_signing_algorithm', 'HS256', '', (
			'HS256' === $value
		) );
		$this->render_radio_button( 'wpa0_client_signing_algorithm_rs', 'client_signing_algorithm', 'RS256', '', (
			'RS256' === $value
		) );

		$this->render_field_description(
			sprintf( __( 'Default new Client value is %s; ', 'wp-auth0' ), WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG ) .
			__( 'found at Client > Show Advanced Settings > OAuth > "JsonWebToken Signature Algorithm" in your ',
				'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' )
		);
	}

	/**
	 * Render cache_expiration and delete cache button
	 */
	public function render_cache_expiration() {
		$this->render_text_field( 'wpa0_cache_expiration', 'cache_expiration', 'number' );
		printf(
			' <input type="button" id="auth0_delete_cache_transient" value="%s" class="button button-secondary">',
			__( 'Delete Cache', 'wp-auth0' )
		);
		$this->render_field_description( __( 'JWKS cache expiration in minutes; set to 0 for no caching', 'wp-auth0' ) );

		if ( $domain = $this->options->get( 'domain' ) ) {
			$this->render_field_description(
				sprintf( '<a href="https://%s/.well-known/jwks.json" target="_blank">%s</a>',
					$domain,
					__( 'View your JWKS here', 'wp-auth0' )
				)
			);
		}
	}

	/**
	 * Render app_token field (should never actually be displayed)
	 */
	public function render_auth0_app_token() {
		$this->render_text_field( 'wpa0_auth0_app_token', 'auth0_app_token', 'password' );

		$this->render_field_description(
			__( 'This token should be ', 'wp-auth0' ) .
			$this->get_docs_link( 'api/management/v2/tokens#get-a-token-manually', __( 'generated manually', 'wp-auth0' ) ) .
			__( ' with the following scopes', 'wp-auth0' ) . ': ' .
			'<br><code>' . implode( '</code>, <code>', WP_Auth0_Api_Client::ConsentRequiredScopes() ) . '</code>'
		);
	}

	/**
	 * Render wordpress_login_enabled
	 */
	public function render_allow_wordpress_login() {
		$this->render_switch( 'wpa0_wp_login_enabled', 'wordpress_login_enabled' );
		$this->render_field_description(
			__( 'Turn on to enable a link on wp-login.php pointing to the core login form; ', 'wp-auth0' ) .
			__( 'this is typically only used while testing the plugin initially or on staging', 'wp-auth0' )
		);
	}

	/**
	 * Render text to say whether user registrations are on or not
	 */
	public function render_allow_signup() {

		if ( is_multisite() ) {
			$settings_text = __( '"Allow new registrations" in the Network Admin > Settings > Network Settings', 'wp-auth0' );
		} else {
			$settings_text = __( '"Anyone can register" in the WordPress General Settings', 'wp-auth0' );
		}

		$allow_signup = $this->options->is_wp_registration_enabled();
		$this->render_field_description(
			__( 'Signups are currently  ', 'wp-auth0' ) .
			'<strong>' . ( $allow_signup ? __( 'enabled', 'wp-auth0' ) : __( 'disabled', 'wp-auth0' ) ) . '</strong>' .
			__( ' by the setting ' ) . $settings_text
		);
	}

	/**
	 * AJAX handler for Delete Cache button
	 */
	public function auth0_delete_cache_transient() {
		check_ajax_referer( 'auth0_delete_cache_transient' );
		delete_transient('WP_Auth0_JWKS_cache');
		die();
	}

	/**
	 * Validate settings being saved
	 *
	 * @param array $old_options - options array before saving
	 * @param array $input - options array after saving
	 *
	 * @return array
	 */
	public function basic_validation( $old_options, $input ) {

		if ( wp_cache_get( 'doing_db_update', WPA0_CACHE_GROUP ) ) {
			return $input;
		}

		$input['client_id'] = sanitize_text_field( $input['client_id'] );
		$input['cache_expiration'] = absint( $input['cache_expiration'] );

		$input['wordpress_login_enabled'] = ( isset( $input['wordpress_login_enabled'] )
			? $input['wordpress_login_enabled']
			: 0 );

		$input['allow_signup'] = ( isset( $input['allow_signup'] ) ? $input['allow_signup'] : 0 );

		// Only replace the secret or token if a new value was set. If not, we will keep the last one entered.
		$input['client_secret'] = ( ! empty( $input['client_secret'] )
			? $input['client_secret']
			: $old_options['client_secret'] );

		$input['client_secret_b64_encoded'] = ( isset( $input['client_secret_b64_encoded'] )
			? $input['client_secret_b64_encoded'] == 1
			: false );

		$input['auth0_app_token'] = ( ! empty( $input['auth0_app_token'] )
			? $input['auth0_app_token']
			: $old_options['auth0_app_token'] );

		// If we have an app token, get and store the audience
		if ( ! empty( $input['auth0_app_token'] ) ) {
			$db_manager = new WP_Auth0_DBManager( WP_Auth0_Options::Instance() );

			if ( get_option( 'wp_auth0_client_grant_failed' ) ) {
				$db_manager->install_db( 16, $input['auth0_app_token'] );
			}

			if ( get_option( 'wp_auth0_grant_types_failed' ) ) {
				$db_manager->install_db( 17, $input['auth0_app_token'] );
			}
		}

		if ( empty( $input['domain'] ) ) {
			$this->add_validation_error( __( 'You need to specify a domain', 'wp-auth0' ) );
		}

		if ( empty( $input['client_id'] ) ) {
			$this->add_validation_error( __( 'You need to specify a client id', 'wp-auth0' ) );
		}

		if ( empty( $input['client_secret'] ) && empty( $old_options['client_secret'] ) ) {
			$this->add_validation_error( __( 'You need to specify a client secret', 'wp-auth0' ) );
		}

		return $input;
	}
}