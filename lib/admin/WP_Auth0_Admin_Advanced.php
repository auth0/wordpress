<?php

class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

	protected $router;
	protected $description = 'Settings related to specific scenarios.';
  protected $actions_middlewares = array(
    'basic_validation',
    'migration_ws_validation',
    'link_accounts_validation',
    'connections_validation',
    'loginredirection_validation',
  );

  public function __construct( WP_Auth0_Options_Generic $options, WP_Auth0_Routes $router ) {
    parent::__construct( $options );
    $this->router = $router;
  }

  public function init() {
    $advancedOptions = array(
	    array( 'id' => 'wpa0_verified_email', 'name' => 'Requires verified email',
	           'function' => 'render_verified_email' ),
	    array( 'id' => 'wpa0_remember_users_session', 'name' => 'Remember users session',
	           'function' => 'render_remember_users_session' ),
	    array( 'id' => 'wpa0_default_login_redirection', 'name' => 'Login redirection URL',
	           'function' => 'render_default_login_redirection' ),
      array( 'id' => 'wpa0_passwordless_enabled', 'name' => 'Use passwordless login',
             'function' => 'render_passwordless_enabled' ),
      array( 'id' => 'wpa0_passwordless_method', 'name' => 'Passwordless method',
             'function' => 'render_passwordless_method' ),
      array( 'id' => 'wpa0_force_https_callback', 'name' => 'Force HTTPS callback',
             'function' => 'render_force_https_callback' ),
      array( 'id' => 'wpa0_cdn_url', 'name' => 'Lock JS CDN URL',
             'function' => 'render_cdn_url' ),
      array( 'id' => 'wpa0_connections', 'name' => 'Connections',
             'function' => 'render_connections' ),
      array( 'id' => 'wpa0_link_auth0_users', 'name' => 'Link users with same email',
             'function' => 'render_link_auth0_users' ),
      array( 'id' => 'wpa0_social_twitter_key', 'name' => 'Twitter consumer key',
             'function' => 'render_social_twitter_key' ),
      array( 'id' => 'wpa0_social_twitter_secret', 'name' => 'Twitter consumer secret',
             'function' => 'render_social_twitter_secret' ),
      array( 'id' => 'wpa0_social_facebook_key', 'name' => 'Facebook app key',
             'function' => 'render_social_facebook_key' ),
      array( 'id' => 'wpa0_social_facebook_secret', 'name' => 'Facebook app secret',
             'function' => 'render_social_facebook_secret' ),
	    array( 'id' => 'wpa0_auto_provisioning', 'name' => 'Auto provisioning',
	           'function' => 'render_auto_provisioning' ),
      array( 'id' => 'wpa0_migration_ws', 'name' => 'Users Migration',
             'function' => 'render_migration_ws' ),
      array( 'id' => 'wpa0_migration_ws_ips_filter', 'name' => 'Migration IPs Whitelist',
             'function' => 'render_migration_ws_ips_filter' ),
      array( 'id' => 'wpa0_migration_ws_ips', 'name' => '',
             'function' => 'render_migration_ws_ips' ),
      array( 'id' => 'wpa0_auth0_implicit_workflow', 'name' => 'Auth0 Implicit flow',
             'function' => 'render_auth0_implicit_workflow' ),
      array( 'id' => 'wpa0_auto_login', 'name' => 'Auto Login (no widget)',
             'function' => 'render_auto_login' ),
      array( 'id' => 'wpa0_auto_login_method', 'name' => 'Auto Login Method',
             'function' => 'render_auto_login_method' ),
      array( 'id' => 'wpa0_ip_range_check', 'name' => 'Enable on IP Ranges',
             'function' => 'render_ip_range_check' ),
      array( 'id' => 'wpa0_ip_ranges', 'name' => 'IP Ranges',
             'function' => 'render_ip_ranges' ),
      array( 'id' => 'wpa0_valid_proxy_ip', 'name' => 'Valid Proxy IP',
             'function' => 'render_valid_proxy_ip' ),
      array( 'id' => 'wpa0_custom_signup_fields', 'name' => 'Custom signup fields',
             'function' => 'render_custom_signup_fields' ),
      array( 'id' => 'wpa0_extra_conf', 'name' => 'Extra settings',
             'function' => 'render_extra_conf' ),
      array( 'id' => 'wpa0_auth0_server_domain', 'name' => 'Auth0 server domain',
             'function' => 'render_auth0_server_domain' ),
      array( 'id' => 'wpa0_metrics', 'name' => 'Anonymous data',
             'function' => 'render_metrics' ),
    );

    if ( WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled() ) {
      $advancedOptions[] = array(
      	'id' => 'wpa0_jwt_auth_integration',
	      'name' => 'Enable JWT Auth integration',
	      'function' => 'render_jwt_auth_integration'
      );
    }

    $this->init_option_section( '', 'advanced', $advancedOptions );
  }

	/**
	 * Render verified email switch
	 */
	public function render_verified_email() {
		$this->render_switch( 'wpa0_verified_email', 'requires_verified_email' );
		$this->render_field_description(
			__( 'Require new users to verify their email before logging in. ', 'wp-auth0' ) .
			__( 'This will disallow logins from social connections that do no provide email (like Twitter)', 'wp-auth0' )
		);
	}

	/**
	 * Render remember_users_session switch
	 */
	public function render_remember_users_session() {
		$this->render_switch( 'wpa0_remember_users_session', 'remember_users_session' );
		$this->render_field_description(
			__( 'WordPress user sessions are, by default, two days. ', 'wp-auth0' ) .
			__( 'Enabling this setting will extend the session to 14 days', 'wp-auth0' )
		);
	}

	/**
	 * Render login redirect field
	 */
	public function render_default_login_redirection() {
		$this->render_text_field( 'wpa0_default_login_redirection', 'default_login_redirection' );
		$this->render_field_description( __( 'URL where successfully logged-in users are redirected to', 'wp-auth0' ) );
	}

	/**
	 * Render Passwordless switch
	 */
	public function render_passwordless_enabled() {
		$this->render_switch( 'wpa0_passwordless_enabled', 'passwordless_enabled' );
		$this->render_field_description( __( 'Username and password login are not enabled when this is on ', 'wp-auth0' )	);
	}

	/**
	 * Render the Passwordless method options; only shows when passwordless_enabled is on
	 */
	public function render_passwordless_method() {
		$value = $this->options->get( 'passwordless_method' );
		$name = 'passwordless_method';

		$this->render_radio_button( 'wpa0_passwordless_method_magiclink', $name, 'magiclink',
			'Magic link', ( empty( $value ) || 'magiclink' === $value ) );
		$this->render_radio_button( 'wpa0_passwordless_method_social', $name, 'social',
			'Social', 'social' === $value );
		$this->render_radio_button( 'wpa0_passwordless_method_sms', $name, 'sms',
			'SMS', 'sms' === $value );
		$this->render_radio_button( 'wpa0_passwordless_method_social_sms', $name, 'socialOrSms',
			'Social or SMS', 'socialOrSms' === $value );
		$this->render_radio_button( 'wpa0_passwordless_method_social_magiclink', $name, 'socialOrMagiclink',
			'Social or Magic Link', 'socialOrMagiclink' === $value );
		$this->render_radio_button( 'wpa0_passwordless_method_emailcode', $name, 'emailcode',
			'Email code', 'emailcode' === $value );
		$this->render_radio_button( 'wpa0_passwordless_method_social_emailcode', $name, 'socialOrEmailcode',
			'Social or Email Code', 'socialOrEmailcode' === $value );

		$this->render_field_description(
			__( 'For more info about password policies, see the ', 'wp-auth0' ) .
			$this->get_docs_link( 'password-strength', __( 'help page on password strength', 'wp-auth0' ) )
		);
	}

	/**
	 * Render force HTTPS switch
	 */
	public function render_force_https_callback() {
		$this->render_switch( 'wpa0_force_https_callback', 'force_https_callback' );
		$this->render_field_description(
			__( 'Forces the plugin to use HTTPS for the callback URL when a site supports both. ', 'wp-auth0' ) .
			__( 'If disabled, it will pick the protocol from the WordPress home URL. ', 'wp-auth0' )
		);
	}

	/**
	 * Render Lock CDN URL field; switches between fields based on passwordless being enabled or not
	 */
	public function render_cdn_url() {
		$pwl_on = $this->options->get( 'passwordless_enabled' );

		$this->render_text_field( 'wpa0_cdn_url', 'cdn_url', 'text', '',
			( $pwl_on ? 'display:none' : '' ) );
		$this->render_text_field( 'wpa0_passwordless_cdn_url', 'passwordless_cdn_url', 'text', '',
			( $pwl_on ? '' : 'display:none' ) );

		$this->render_field_description(
			__( 'This should point to the latest widget JS available in the CDN and rarely needs to change', 'wp-auth0' )
		);
	}

	/**
	 * Render connections to show on Lock
	 */
	public function render_connections() {
		$this->render_text_field( 'wpa0_connections', 'lock_connections' );
		$this->render_field_description(
			__( 'Which connections the Auth0 login form should show; separate multiple with commas. ', 'wp-auth0' ) .
			__( 'This is ignored when empty and is mandatory for passwordless with social mode. ', 'wp-auth0' )
		);
	}

	/**
	 * Render link_aut0_users switch
	 */
	public function render_link_auth0_users() {
		$this->render_switch( 'wpa0_link_auth0_users', 'link_auth0_users' );
		$this->render_field_description(
			__( 'Links accounts with the same e-mail address. ', 'wp-auth0' ) .
			__( 'It will only occur if both e-mails are previously verified', 'wp-auth0' )
		);
	}

	/**
	 * Render Twitter key field
	 */
	public function render_social_twitter_key() {
		$this->render_social_key_field( 'wpa0_social_twitter_key', 'social_twitter_key' );
	}

	/**
	 * Render Twitter secret field
	 */
	public function render_social_twitter_secret() {
		$this->render_social_key_field( 'wpa0_social_twitter_secret', 'social_twitter_secret' );
	}

	/**
	 * Render Facebook key field
	 */
	public function render_social_facebook_key() {
		$this->render_social_key_field( 'wpa0_social_facebook_key', 'social_facebook_key' );
	}

	/**
	 * Render Facebook secret field
	 */
	public function render_social_facebook_secret() {
		$this->render_social_key_field( 'wpa0_social_facebook_secret', 'social_facebook_secret' );
	}

	/**
	 * Render auto_provisioning switch
	 */
	public function render_auto_provisioning() {
		$this->render_switch( 'wpa0_auto_provisioning', 'auto_provisioning' );
		$this->render_field_description(
			__( 'Enable this setting to create new users in the WordPress database when signups are off. ', 'wp-auth0' ) .
			__( 'Users that are created in Auth0 and successfully log in will be added', 'wp-auth0' )
		);
	}

	/**
	 * Render migration WS switch and token field, if enabled
	 */
	public function render_migration_ws() {
		$value = $this->options->get( 'migration_ws' );
		$this->render_switch( 'wpa0_auth0_migration_ws', 'migration_ws' );

		if ( $value ) {

			$this->render_field_description(
				__( 'Users migration is enabled. ', 'wp-auth0' ) .
				__( 'If you disable this setting, it must be re-enabled manually in the ', 'wp-auth0' ) .
				$this->get_dashboard_link( 'connections' )
			);
			$this->render_field_description( 'Security token:' );
			printf(
				'<textarea class="code" rows="%d" disabled>%s</textarea>',
				$this->textarea_rows,
				sanitize_text_field(  $this->options->get( 'migration_token' ) )
			);
		} else {

			$this->render_field_description(
				__( 'Users migration is disabled. ', 'wp-auth0' ) .
				__( 'Enabling this exposes migration webservices but the Connection must be updated manually. ', 'wp-auth0' ) .
				$this->get_docs_link( 'connections/database/migrating', __( 'More information here', 'wp-auth0' ) )
			);
		}
	}

	/**
	 * Render migration IP whitelist switch
	 */
	public function render_migration_ws_ips_filter() {
		$this->render_switch( 'wpa0_auth0_migration_ips_filter', 'migration_ips_filter' );
	}

	/**
	 * Render migration IP whitelist field
	 */
	public function render_migration_ws_ips() {
		$this->render_textarea_field( 'wpa0_auth0_migration_ips', 'migration_ips' );
		$this->render_field_description(
			__( 'Only requests from this IPs will be allowed to the migration WS. ', 'wp-auth0' ) .
			__( 'Separate multiple IPs with commas', 'wp-auth0' )
		);
	}

	/**
	 * Render Implicit flow switch
	 */
	public function render_auth0_implicit_workflow() {
		$this->render_switch( "wpa0_auth0_implicit_workflow", "auth0_implicit_workflow" );
		$this->render_field_description(
			__( 'Turns on implicit login flow. Your Client should be set to "Single Page App" in your ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' )
		);
	}

	/**
	 * Render auto-login switch
	 */
	public function render_auto_login() {
		$this->render_switch( 'wpa0_auto_login', 'auto_login' );
		$this->render_field_description(
			__( 'Send logins directly to a specific Connection, skipping the login page', 'wp-auth0' )
		);
	}

	/**
	 * Render auto-login method field
	 */
	public function render_auto_login_method() {
		$this->render_text_field( 'wpa0_auto_login_method', 'auto_login_method' );
		$this->render_field_description(
			__( 'Find the method name to use under Connections > [Connection Type] in your ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'connections' ) .
			__( '. Click the expand icon and use the value in the "Name" field (like "google-oauth2")', 'wp-auth0' )
		);
	}

	/**
	 * Render IP range switch
	 */
  public function render_ip_range_check() {
    $this->render_switch( 'wpa0_ip_range_check', 'ip_range_check' );
  }

	/**
	 * Render IP range field
	 */
  public function render_ip_ranges() {
	  $this->render_textarea_field( 'wpa0_ip_ranges', 'ip_ranges' );
	  $this->render_field_description(
		  __( 'Only one range per line! Range format should be as follows (spaces ignored): ', 'wp-auth0' ) .
		  __( '<br><code>xx.xx.xx.xx - yy.yy.yy.yy</code>', 'wp-auth0' )
	  );
  }

	/**
	 * Render proxy IP field
	 */
  public function render_valid_proxy_ip() {
	  $this->render_text_field( 'wpa0_valid_proxy_ip', 'valid_proxy_ip' );
	  $this->render_field_description(
		  __( 'Whitelist for proxy and load balancer IPs to enable logins and migration webservices', 'wp-auth0' )
	  );
  }

	/**
	 * Render custom signup JSON field and example
	 */
	public function render_custom_signup_fields() {
		$this->render_textarea_field( 'wpa0_custom_signup_fields', 'custom_signup_fields' );
		$this->render_field_description(
			__( 'JSON that describes custom signup fields in Auth0 login form; example below. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'docs/libraries/lock/v10/new-features#custom-sign-up-fields',
				__( 'More information here', 'wp-auth0' )
			)
		);
		$this->render_field_description( '<pre>[
  {
    name: "address",                              // required
    placeholder: "enter your address",            // required
    icon: "https://example.com/address_icon.png", // optional
    prefill: "street 123",                        // optional
    validator: function(value) {                  // optional
      // only accept addresses with more than 10 chars
      return value.length > 10;
    }
  },
  {
    ... // more fields could be specified
  }
]</pre>'
		);
	}

	/**
	 * Render additional Lock configuration JSON field
	 */
	public function render_extra_conf() {
		$this->render_textarea_field( 'wpa0_extra_conf', 'extra_conf' );
		$this->render_field_description(
			__( 'Valid JSON for Lock options configuration; will override all options set elsewhere. ', 'wp-auth0' ) .
			$this->get_docs_link( 'libraries/lock/customization', 'See options and examples' )
		);
	}

	/**
	 * Render Auth0 server domain field
	 */
  public function render_auth0_server_domain() {
	  $this->render_text_field( 'wpa0_auth0_server_domain', 'auth0_server_domain' );
	  $this->render_field_description(
	  	__( 'The Auth0 domain, it is used by the setup wizard to fetch your account information', 'wp-auth0' )
	  );
  }

	/**
	 * Render anonymous metrics switch
	 */
  public function render_metrics() {
    $this->render_switch( 'wpa0_metrics', 'metrics' );
	  $this->render_field_description(
	  	__( 'Enables anonymous usage data reporting (WP, PHP, and plugin versions)', 'wp-auth0' )
	  );
  }

	/**
	 * Render JWT integration switch
	 */
	public function render_jwt_auth_integration() {
		$this->render_switch( 'wpa0_jwt_auth_integration', 'jwt_auth_integration' );
		$this->render_field_description( __( 'This will enable the JWT Auth Users Repository override', 'wp-auth0' ) );
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
    $input['requires_verified_email'] = ( isset( $input['requires_verified_email'] ) ? $input['requires_verified_email'] : 0 );
    $input['auto_provisioning'] = ( isset( $input['auto_provisioning'] ) ? $input['auto_provisioning'] : 0 );
    $input['remember_users_session'] = ( isset( $input['remember_users_session'] ) ? $input['remember_users_session'] : 0 ) == 1;
    $input['passwordless_enabled'] = ( isset( $input['passwordless_enabled'] ) ? $input['passwordless_enabled'] : 0 ) == 1;
    $input['jwt_auth_integration'] = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
    $input['auth0_implicit_workflow'] = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
    $input['metrics'] = ( isset( $input['metrics'] ) ? $input['metrics'] : 0 );
    $input['force_https_callback'] = ( isset( $input['force_https_callback'] ) ? $input['force_https_callback'] : 0 );
    $input['default_login_redirection'] = esc_url_raw( $input['default_login_redirection'] );

    if ( isset( $input['connections'] ) ) {
      if ( isset( $input['connections']['social_twitter_key'] ) ) $input['connections']['social_twitter_key'] = sanitize_text_field( $input['connections']['social_twitter_key'] );
      if ( isset( $input['connections']['social_twitter_secret'] ) ) $input['connections']['social_twitter_secret'] = sanitize_text_field( $input['connections']['social_twitter_secret'] );
      if ( isset( $input['connections']['social_facebook_key'] ) ) $input['connections']['social_facebook_key'] = sanitize_text_field( $input['connections']['social_facebook_key'] );
      if ( isset( $input['connections']['social_facebook_secret'] ) ) $input['connections']['social_facebook_secret'] = sanitize_text_field( $input['connections']['social_facebook_secret'] );
    }

    $input['migration_ips_filter'] =  ( ! empty( $input['migration_ips_filter'] ) ? 1 : 0 );
    $input['migration_ips'] = sanitize_text_field( $input['migration_ips'] );

    $input['valid_proxy_ip'] = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );

    $input['lock_connections'] = trim( $input['lock_connections'] );
    $input['custom_signup_fields'] = trim( $input['custom_signup_fields'] );

    if ( ! empty( $input['passwordless_enabled'] ) && empty( $input['lock_connections'] ) && strpos( strtolower( $input['passwordless_method'] ), 'social' ) !== false ) {
      $error = __( "Please complete the list of connections to be used by Lock in social mode.", "wp-auth0" );
      self::add_validation_error( $error );
    }

    if ( trim( $input["extra_conf"] ) != '' ) {
      if ( json_decode( $input["extra_conf"] ) === null ) {
        $error = __( "The Extra settings parameter should be a valid json object", "wp-auth0" );
        self::add_validation_error( $error );
      }
    }

    return $input;
  }

  public function migration_ws_validation( $old_options, $input ) {
    $input['migration_ws'] = ( isset( $input['migration_ws'] ) ? $input['migration_ws'] : 0 );

    if ( $old_options['migration_ws'] != $input['migration_ws'] ) {

      if ( 1 == $input['migration_ws'] ) {

	      $token_id = uniqid();
	      $secret = $input['client_secret'];
	      if ( $input['client_secret_b64_encoded'] ) {
		      $secret = JWT::urlsafeB64Decode( $secret );
	      }

        $input['migration_token'] = JWT::encode( array( 'scope' => 'migration_ws', 'jti' => $token_id ), $secret );
        $input['migration_token_id'] = $token_id;

	      $this->add_validation_error(
		      __( 'User Migration needs to be configured manually. ', 'wp-auth0' )
		      . __( 'Please see Advanced > Users Migration below for your token, instructions are ', 'wp-auth0' )
		      . '<a href="https://auth0.com/docs/users/migrations/automatic">HERE</a>.'
	      );

      } else {
        $input['migration_token'] = null;
        $input['migration_token_id'] = null;

        if (isset($old_options['db_connection_id'])) {


          $connection = WP_Auth0_Api_Client::get_connection($input['domain'], $input['auth0_app_token'], $old_options['db_connection_id']);

          $connection->options->enabledDatabaseCustomization = false;
          $connection->options->import_mode = false;


          unset($connection->name);
          unset($connection->strategy);
          unset($connection->id);

          $response = WP_Auth0_Api_Client::update_connection($input['domain'], $input['auth0_app_token'], $old_options['db_connection_id'], $connection);
        } else {
          $response = false;
        }

        if ( $response === false ) {
          $error = __( 'There was an error disabling your custom database. Check how to do it manually ', 'wp-auth0' );
          $error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
          $this->add_validation_error( $error );
        }
      }

      $this->router->setup_rewrites( $input['migration_ws'] == 1 );
      flush_rewrite_rules();
    } else {
      $input['migration_token'] = $old_options['migration_token'];
      $input['migration_token_id'] = $old_options['migration_token_id'];
    }
    return $input;
  }

  public function link_accounts_validation( $old_options, $input ) {
    $link_script = WP_Auth0_RulesLib::$link_accounts['script'];
    $link_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $link_script );
    $link_script = str_replace( 'REPLACE_WITH_YOUR_DOMAIN', $input['domain'], $link_script );
    $link_script = str_replace( 'REPLACE_WITH_YOUR_API_TOKEN', $input['auth0_app_token'], $link_script );
    return $this->rule_validation($old_options, $input, 'link_auth0_users', WP_Auth0_RulesLib::$link_accounts['name'] . '-' . get_auth0_curatedBlogName(), $link_script);
  }

  public function connections_validation( $old_options, $input ) {

    $check_if_enabled = array();
    $passwordless_connections = array(
      'sms' => 'sms',
      'magiclink' => 'email',
      'emailcode' => 'email');

    if (! empty( $input['passwordless_enabled'] ) && $input['passwordless_enabled'] != $old_options['passwordless_enabled']) {

      // $check_if_enabled = explode(',', $input['lock_connections']);

      foreach ($passwordless_connections as $alias => $name) {
        if (strpos($input['passwordless_method'], $alias) !== false) {
          $check_if_enabled[] = $name;
        }
      }

    } elseif ($input['passwordless_method'] != $old_options['passwordless_method']) {

      // $check_if_enabled = explode(',', $input['lock_connections']);

      foreach ($passwordless_connections as $name) {
        if (strpos($input['passwordless_method'], $name) !== false) {
          $check_if_enabled[] = $name;
        }
      }

    } // elseif ($input['lock_connections'] != $old_options['lock_connections']) {

    //   $check_if_enabled = explode(',', $input['lock_connections']);

    // }

    if (!empty($check_if_enabled)) {

      $response = WP_Auth0_Api_Client::search_connection($input['domain'], $input['auth0_app_token']);
      $enabled_connections = array();
      foreach ($response as $connection) {
        if (in_array($input['client_id'], $connection->enabled_clients)) {
          $enabled_connections[] = $connection->strategy;
        }
      }

      $matching = array_intersect($enabled_connections, $check_if_enabled);

      if (array_diff($matching, $check_if_enabled) !== array_diff($check_if_enabled, $matching)) {
        $error = __( 'The passwordless connection is not enabled. Please go to the Auth0 Dashboard and configure it.', 'wp-auth0' );
        $this->add_validation_error( $error );
      }

    }

    return $input;
  }

  public function loginredirection_validation( $old_options, $input ) {
    $home_url = home_url();

    if ( empty( $input['default_login_redirection'] ) ) {
      $input['default_login_redirection'] = $home_url;
    } else {
      if ( strpos( $input['default_login_redirection'], $home_url ) !== 0 ) {
        if ( strpos( $input['default_login_redirection'], 'http' ) === 0 ) {
          $input['default_login_redirection'] = $home_url;
          $error = __( "The 'Login redirect URL' cannot point to a foreign page.", "wp-auth0" );
          $this->add_validation_error( $error );
        }
      }

      if ( strpos( $input['default_login_redirection'], 'action=logout' ) !== false ) {
        $input['default_login_redirection'] = $home_url;

        $error = __( "The 'Login redirect URL' cannot point to the logout page. ", "wp-auth0" );
        $this->add_validation_error( $error );
      }
    }
    return $input;
  }
}