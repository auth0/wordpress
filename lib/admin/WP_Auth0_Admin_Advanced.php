<?php

class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

	// TODO: Deprecate
	const ADVANCED_DESCRIPTION = 'Settings related to specific scenarios.';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
		'migration_ws_validation',
		'link_accounts_validation',
		'loginredirection_validation',
	);

	protected $router;

	/**
	 * WP_Auth0_Admin_Advanced constructor.
	 *
	 * @param WP_Auth0_Options_Generic $options
	 * @param WP_Auth0_Routes          $router
	 */
	public function __construct( WP_Auth0_Options_Generic $options, WP_Auth0_Routes $router ) {
		parent::__construct( $options );
		$this->router       = $router;
		$this->_description = __( 'Settings related to specific scenarios.', 'wp-auth0' );
	}

	/**
	 * All settings in the Advanced tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = array(
			array(
				'name'     => __( 'Require Verified Email', 'wp-auth0' ),
				'opt'      => 'requires_verified_email',
				'id'       => 'wpa0_verified_email',
				'function' => 'render_verified_email',
			),
			array(
				'name'     => __( 'Remember User Session', 'wp-auth0' ),
				'opt'      => 'remember_users_session',
				'id'       => 'wpa0_remember_users_session',
				'function' => 'render_remember_users_session',
			),
			array(
				'name'     => __( 'Login Redirection URL', 'wp-auth0' ),
				'opt'      => 'default_login_redirection',
				'id'       => 'wpa0_default_login_redirection',
				'function' => 'render_default_login_redirection',
			),
			array(
				'name'     => __( 'Connections to Show', 'wp-auth0' ),
				'opt'      => 'lock_connections',
				'id'       => 'wpa0_connections',
				'function' => 'render_connections',
			),
			array(
				'name'     => __( 'Force HTTPS Callback', 'wp-auth0' ),
				'opt'      => 'force_https_callback',
				'id'       => 'wpa0_force_https_callback',
				'function' => 'render_force_https_callback',
			),
			array(
				'name'     => __( 'Lock JS CDN URL', 'wp-auth0' ),
				'opt'      => 'cdn_url',
				'id'       => 'wpa0_cdn_url',
				'function' => 'render_cdn_url',
			),
			array(
				'name'     => __( 'Link Users with Same Email', 'wp-auth0' ),
				'opt'      => 'link_auth0_users',
				'id'       => 'wpa0_link_auth0_users',
				'function' => 'render_link_auth0_users',
			),
			array(
				'name'     => __( 'Auto Provisioning', 'wp-auth0' ),
				'opt'      => 'auto_provisioning',
				'id'       => 'wpa0_auto_provisioning',
				'function' => 'render_auto_provisioning',
			),
			array(
				'name'     => __( 'User Migration', 'wp-auth0' ),
				'opt'      => 'migration_ws',
				'id'       => 'wpa0_migration_ws',
				'function' => 'render_migration_ws',
			),
			array(
				'name'     => __( 'Migration IPs Whitelist', 'wp-auth0' ),
				'opt'      => 'migration_ips_filter',
				'id'       => 'wpa0_migration_ws_ips_filter',
				'function' => 'render_migration_ws_ips_filter',
			),
			array(
				'name'     => __( 'IP Addresses', 'wp-auth0' ),
				'opt'      => 'migration_ips',
				'id'       => 'wpa0_migration_ws_ips',
				'function' => 'render_migration_ws_ips',
			),
			array(
				'name'     => __( 'Auto Login', 'wp-auth0' ),
				'opt'      => 'auto_login',
				'id'       => 'wpa0_auto_login',
				'function' => 'render_auto_login',
			),
			array(
				'name'     => __( 'Auto Login Method', 'wp-auth0' ),
				'opt'      => 'auto_login_method',
				'id'       => 'wpa0_auto_login_method',
				'function' => 'render_auto_login_method',
			),
			array(
				'name'     => __( 'Implicit Login Flow', 'wp-auth0' ),
				'opt'      => 'auth0_implicit_workflow',
				'id'       => 'wpa0_auth0_implicit_workflow',
				'function' => 'render_auth0_implicit_workflow',
			),
			array(
				'name'     => __( 'Enable IP Ranges', 'wp-auth0' ),
				'opt'      => 'ip_range_check',
				'id'       => 'wpa0_ip_range_check',
				'function' => 'render_ip_range_check',
			),
			array(
				'name'     => __( 'IP Ranges', 'wp-auth0' ),
				'opt'      => 'ip_ranges',
				'id'       => 'wpa0_ip_ranges',
				'function' => 'render_ip_ranges',
			),
			array(
				'name'     => __( 'Valid Proxy IP', 'wp-auth0' ),
				'opt'      => 'valid_proxy_ip',
				'id'       => 'wpa0_valid_proxy_ip',
				'function' => 'render_valid_proxy_ip',
			),
			array(
				'name'     => __( 'Extra Settings', 'wp-auth0' ),
				'opt'      => 'extra_conf',
				'id'       => 'wpa0_extra_conf',
				'function' => 'render_extra_conf',
			),
			array(
				'name'     => __( 'Custom Signup Fields', 'wp-auth0' ),
				'opt'      => 'custom_signup_fields',
				'id'       => 'wpa0_custom_signup_fields',
				'function' => 'render_custom_signup_fields',
			),
			array(
				'name'     => __( 'Twitter Consumer Key', 'wp-auth0' ),
				'opt'      => 'social_twitter_key',
				'id'       => 'wpa0_social_twitter_key',
				'function' => 'render_social_twitter_key',
			),
			array(
				'name'     => __( 'Twitter Consumer Secret', 'wp-auth0' ),
				'opt'      => 'social_twitter_secret',
				'id'       => 'wpa0_social_twitter_secret',
				'function' => 'render_social_twitter_secret',
			),
			array(
				'name'     => __( 'Facebook App Key', 'wp-auth0' ),
				'opt'      => 'social_facebook_key',
				'id'       => 'wpa0_social_facebook_key',
				'function' => 'render_social_facebook_key',
			),
			array(
				'name'     => __( 'Facebook App Secret', 'wp-auth0' ),
				'opt'      => 'social_facebook_secret',
				'id'       => 'wpa0_social_facebook_secret',
				'function' => 'render_social_facebook_secret',
			),
			array(
				'name'     => __( 'Auth0 Server Domain', 'wp-auth0' ),
				'opt'      => 'auth0_server_domain',
				'id'       => 'wpa0_auth0_server_domain',
				'function' => 'render_auth0_server_domain',
			),
			array(
				'name'     => __( 'Report Anonymous Data', 'wp-auth0' ),
				'opt'      => 'metrics',
				'id'       => 'wpa0_metrics',
				'function' => 'render_metrics',
			),
		);

		if ( WP_Auth0_Configure_JWTAUTH::is_jwt_auth_enabled() ) {
			$options[] = array(
				'name'     => 'Enable JWT Auth Integration',
				'opt'      => 'jwt_auth_integration',
				'id'       => 'wpa0_jwt_auth_integration',
				'function' => 'render_jwt_auth_integration',
			);
		}

		$this->init_option_section( '', 'advanced', $options );
	}

	public function render_verified_email( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Require new users to both provide and verify their email before logging in. ', 'wp-auth0' ) .
			__( 'An email is verified manually by an email from Auth0 or automatically by the provider. ', 'wp-auth0' ) .
			__( 'This will disallow logins from social connections that do not provide email (like Twitter)', 'wp-auth0' )
		);
	}

	public function render_remember_users_session( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'A user session by default is kept for two days. ', 'wp-auth0' ) .
			__( 'Enabling this setting will extend that and make the session be kept for 14 days', 'wp-auth0' )
		);
	}

	public function render_default_login_redirection( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'URL where successfully logged-in users are redirected when using the wp-login.php page. ', 'wp-auth0' ) .
			__( 'This can be overridden with the <code>redirect_to</code> URL parameter', 'wp-auth0' )
		);
	}

	public function render_connections( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', 'eg: "sms, google-oauth2, github"' );
		$this->render_field_description(
			__( 'Specify which Social, Database, or Passwordless connections to display in the Auth0 form. ', 'wp-auth0' ) .
			__( 'If this is empty, all enabled connections for this Application will be shown. ', 'wp-auth0' ) .
			__( 'Separate multiple connection names with a comma. ', 'wp-auth0' ) .
			sprintf(
				__( 'Connections listed here must already be active in your %s', 'wp-auth0' ),
				$this->get_dashboard_link( 'connections/social' )
			) .
			__( ' and enabled for this Application. ', 'wp-auth0' ) .
			__( 'Click on a Connection and use the "Name" value in this field', 'wp-auth0' )
		);
	}

	public function render_force_https_callback( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Forces the plugin to use HTTPS for the callback URL when a site supports both; ', 'wp-auth0' ) .
			__( 'if disabled, the protocol from the WordPress home URL will be used', 'wp-auth0' )
		);
	}

	public function render_cdn_url( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'This should point to the latest Lock JS available in the CDN and rarely needs to change', 'wp-auth0' )
		);
	}

	public function render_link_auth0_users( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Link accounts with the same verified e-mail address. ', 'wp-auth0' ) .
			__( 'See the "Require Verified Email" setting above for more information on email verification', 'wp-auth0' )
		);
	}

	public function render_auto_provisioning( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Create new users in the WordPress database when signups are off. ', 'wp-auth0' ) .
			__( 'Signups will not be allowed but successful Auth0 logins will add the user in WordPress', 'wp-auth0' )
		);
	}

	public function render_migration_ws( $args = array() ) {
		$value = $this->options->get( $args['opt_name'] );
		$this->render_switch( $args['label_for'], $args['opt_name'] );

		if ( $value ) {
			$this->render_field_description(
				__( 'Users migration is enabled. ', 'wp-auth0' ) .
				__( 'If you disable this setting, it must be re-enabled manually in the ', 'wp-auth0' ) .
				$this->get_dashboard_link()
			);
			$this->render_field_description( 'Security token:' );
			printf(
				'<textarea class="code" rows="%d" disabled>%s</textarea>',
				$this->_textarea_rows,
				sanitize_text_field( $this->options->get( 'migration_token' ) )
			);
		} else {
			$this->render_field_description(
				__( 'Users migration is disabled. ', 'wp-auth0' ) .
				__( 'Enabling this exposes migration webservices but the Connection must be updated manually. ', 'wp-auth0' ) .
				$this->get_docs_link( 'users/migrations/automatic', __( 'More information here', 'wp-auth0' ) )
			);
		}
	}

	public function render_migration_ws_ips_filter( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_migration_ws_ips' );
	}

	public function render_migration_ws_ips( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Only requests from these IPs will be allowed to access the migration webservice. ', 'wp-auth0' ) .
			__( 'Separate multiple IPs with commas', 'wp-auth0' )
		);
	}

	public function render_auto_login( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_auto_login_method' );
		$this->render_field_description(
			__( 'Send logins directly to a specific Connection, skipping the login page', 'wp-auth0' )
		);
	}

	public function render_auto_login_method( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			sprintf(
				__( 'Find the method name to use under Connections > [Connection Type] in your %s. ', 'wp-auth0' ),
				$this->get_dashboard_link()
			) .
			__( 'Click the expand icon and use the value in the "Name" field (like "google-oauth2")', 'wp-auth0' )
		);
	}

	public function render_auth0_implicit_workflow( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Turns on implicit login flow, which most sites will not need. ', 'wp-auth0' ) .
			__( 'Only enable this if outbound connections to auth0.com are disabled on your server. ', 'wp-auth0' ) .
			__( 'Your Application should be set to "Single Page App" in your ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'clients' ) .
			__( ' for this setting to work properly. ', 'wp-auth0' ) .
			__( 'This will limit profile changes and other functionality in the plugin', 'wp-auth0' )
		);
	}

	public function render_ip_range_check( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_ip_ranges' );
	}

	public function render_ip_ranges( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Only one range per line! Range format should be as follows (spaces ignored): ', 'wp-auth0' ) .
			__( '<br><code>xx.xx.xx.xx - yy.yy.yy.yy</code>', 'wp-auth0' )
		);
	}

	public function render_valid_proxy_ip( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Whitelist for proxy and load balancer IPs to enable logins and migration webservices', 'wp-auth0' )
		);
	}

	public function render_extra_conf( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Valid JSON for Lock options configuration; will override all options set elsewhere. ', 'wp-auth0' ) .
			$this->get_docs_link( 'libraries/lock/customization', 'See options and examples' )
		);
	}

	public function render_custom_signup_fields( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Valid JSON for additional signup fields in the Auth0 signup form. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'libraries/lock/v11/configuration#additionalsignupfields-array-',
				__( 'More information and examples', 'wp-auth0' )
			)
		);
	}

	public function render_social_twitter_key( $args = array() ) {
		$this->render_social_key_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Used for the Social Amplification Widget. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/twitter#2-get-your-consumer-key-and-consumer-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	public function render_social_twitter_secret( $args = array() ) {
		$this->render_social_key_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Used for the Social Amplification Widget. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/twitter#2-get-your-consumer-key-and-consumer-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	public function render_social_facebook_key( $args = array() ) {
		$this->render_social_key_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Used for the Social Amplification Widget. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/facebook#5-get-your-app-id-and-app-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	public function render_social_facebook_secret( $args = array() ) {
		$this->render_social_key_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Used for the Social Amplification Widget. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/facebook#5-get-your-app-id-and-app-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	public function render_auth0_server_domain( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'The Auth0 domain used by the setup wizard to fetch your account information', 'wp-auth0' )
		);
	}

	public function render_metrics() {
		$v = absint( $this->options->get( 'metrics' ) );

		echo $this->render_a0_switch( 'wpa0_metrics', 'metrics', 1, 1 == $v );
		?>

		<div class="subelement">
		<span class="description">
			<?php echo __( 'This plugin tracks anonymous usage data. Click to disable.', 'wp-auth0' ); ?>
		</span>
		</div>
		<?php
	}

	public function render_jwt_auth_integration( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description( __( 'This will enable the JWT Auth Users Repository override', 'wp-auth0' ) );
	}

	// TODO: Deprecate, moved to WP_Auth0_Admin_features
	public function render_passwordless_enabled( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Turns on Passwordless login (email or SMS) in the Auth0 form. ', 'wp-auth0' ) .
			__( 'Passwordless connections are managed in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'connections/passwordless' ) .
			__( ' and at least one must be active and enabled on this Application for this to work. ', 'wp-auth0' ) .
			__( 'Username/password login is not enabled when Passwordless is on', 'wp-auth0' )
		);
	}

	// TODO: Deprecate
	public function render_passwordless_method() {
		$v = $this->options->get( 'passwordless_method' );
		?>
		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social" value="social" <?php echo checked( $v, 'social', false ); ?>/><label for="wpa0_passwordless_method_social">Social</label>

		<br>

		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_sms" value="sms" <?php echo checked( $v, 'sms', false ); ?>/><label for="wpa0_passwordless_method_sms">SMS</label>

		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social_sms" value="socialOrSms" <?php echo checked( $v, 'socialOrSms', false ); ?>/><label for="wpa0_passwordless_method_social_sms">Social or SMS</label>

		<br>

		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_magiclink" value="magiclink" <?php echo checked( $v, 'magiclink', false ); ?>/><label for="wpa0_passwordless_method_magiclink">Magic Link</label>

		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social_magiclink" value="socialOrMagiclink" <?php echo checked( $v, 'socialOrMagiclink', false ); ?>/><label for="wpa0_passwordless_method_social_magiclink">Social or Magic Link</label>

		<br>

		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_emailcode" value="emailcode" <?php echo checked( $v, 'emailcode', false ); ?>/><label for="wpa0_passwordless_method_emailcode">Email Code</label>

		<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[passwordless_method]" id="wpa0_passwordless_method_social_emailcode" value="socialOrEmailcode" <?php echo checked( $v, 'socialOrEmailcode', false ); ?>/><label for="wpa0_passwordless_method_social_emailcode">Social or Email Code</label>




		<div class="subelement">
		<span class="description">
			<?php echo __( 'For more info about the password policies check ', 'wp-auth0' ); ?>
			<a target="_blank" href="https://auth0.com/docs/password-strength"><?php echo __( 'HERE', 'wp-auth0' ); ?></a>
		</span>
		</div>
		<?php
	}

	// TODO: Deprecate
	public function render_advanced_description() {
		?>

		<p class=\"a0-step-text\"><?php echo self::ADVANCED_DESCRIPTION; ?></p>

		<?php
	}

	public function basic_validation( $old_options, $input ) {
		$input['requires_verified_email']   = ( isset( $input['requires_verified_email'] ) ? $input['requires_verified_email'] : 0 );
		$input['auto_provisioning']         = ( isset( $input['auto_provisioning'] ) ? $input['auto_provisioning'] : 0 );
		$input['remember_users_session']    = ( isset( $input['remember_users_session'] ) ? $input['remember_users_session'] : 0 ) == 1;
		$input['passwordless_enabled']      = ( isset( $input['passwordless_enabled'] ) ? $input['passwordless_enabled'] : 0 ) == 1;
		$input['jwt_auth_integration']      = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
		$input['auth0_implicit_workflow']   = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
		$input['metrics']                   = ( isset( $input['metrics'] ) ? $input['metrics'] : 0 );
		$input['force_https_callback']      = ( isset( $input['force_https_callback'] ) ? $input['force_https_callback'] : 0 );
		$input['default_login_redirection'] = esc_url_raw( $input['default_login_redirection'] );

		if ( isset( $input['connections'] ) ) {
			if ( isset( $input['connections']['social_twitter_key'] ) ) {
				$input['connections']['social_twitter_key'] = sanitize_text_field( $input['connections']['social_twitter_key'] );
			}
			if ( isset( $input['connections']['social_twitter_secret'] ) ) {
				$input['connections']['social_twitter_secret'] = sanitize_text_field( $input['connections']['social_twitter_secret'] );
			}
			if ( isset( $input['connections']['social_facebook_key'] ) ) {
				$input['connections']['social_facebook_key'] = sanitize_text_field( $input['connections']['social_facebook_key'] );
			}
			if ( isset( $input['connections']['social_facebook_secret'] ) ) {
				$input['connections']['social_facebook_secret'] = sanitize_text_field( $input['connections']['social_facebook_secret'] );
			}
		}

		$input['migration_ips_filter'] = ( ! empty( $input['migration_ips_filter'] ) ? 1 : 0 );
		$input['migration_ips']        = sanitize_text_field( $input['migration_ips'] );

		$input['valid_proxy_ip'] = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );

		$input['lock_connections']     = trim( $input['lock_connections'] );
		$input['custom_signup_fields'] = trim( $input['custom_signup_fields'] );

		if ( trim( $input['extra_conf'] ) != '' ) {
			if ( json_decode( $input['extra_conf'] ) === null ) {
				$error = __( 'The Extra settings parameter should be a valid json object', 'wp-auth0' );
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
				$secret   = $input['client_secret'];
				if ( $input['client_secret_b64_encoded'] ) {
					$secret = JWT::urlsafeB64Decode( $secret );
				}

				$input['migration_token']    = JWT::encode(
					array(
						'scope' => 'migration_ws',
						'jti'   => $token_id,
					), $secret
				);
				$input['migration_token_id'] = $token_id;

				$this->add_validation_error(
					__( 'User Migration needs to be configured manually. ', 'wp-auth0' )
					. __( 'Please see Advanced > Users Migration below for your token, instructions are ', 'wp-auth0' )
					. '<a href="https://auth0.com/docs/users/migrations/automatic">HERE</a>.'
				);

			} else {
				$input['migration_token']    = null;
				$input['migration_token_id'] = null;

				if ( isset( $old_options['db_connection_id'] ) ) {

					$connection = WP_Auth0_Api_Client::get_connection( $input['domain'], $input['auth0_app_token'], $old_options['db_connection_id'] );

					$connection->options->enabledDatabaseCustomization = false;
					$connection->options->import_mode                  = false;

					unset( $connection->name );
					unset( $connection->strategy );
					unset( $connection->id );

					$response = WP_Auth0_Api_Client::update_connection( $input['domain'], $input['auth0_app_token'], $old_options['db_connection_id'], $connection );
				} else {
					$response = false;
				}

				if ( $response === false ) {
					$error  = __( 'There was an error disabling your custom database. Check how to do it manually ', 'wp-auth0' );
					$error .= '<a href="https://manage.auth0.com/#/connections/database">HERE</a>.';
					$this->add_validation_error( $error );
				}
			}

			$this->router->setup_rewrites( $input['migration_ws'] == 1 );
			flush_rewrite_rules();
		} else {
			$input['migration_token']    = $old_options['migration_token'];
			$input['migration_token_id'] = $old_options['migration_token_id'];
		}
		return $input;
	}

	public function link_accounts_validation( $old_options, $input ) {
		$link_script = WP_Auth0_RulesLib::$link_accounts['script'];
		$link_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $link_script );
		$link_script = str_replace( 'REPLACE_WITH_YOUR_DOMAIN', $input['domain'], $link_script );
		$link_script = str_replace( 'REPLACE_WITH_YOUR_API_TOKEN', $input['auth0_app_token'], $link_script );
		return $this->rule_validation( $old_options, $input, 'link_auth0_users', WP_Auth0_RulesLib::$link_accounts['name'] . '-' . get_auth0_curatedBlogName(), $link_script );
	}

	// TODO: Deprecate
	public function connections_validation( $old_options, $input ) {

		$check_if_enabled         = array();
		$passwordless_connections = array(
			'sms'       => 'sms',
			'magiclink' => 'email',
			'emailcode' => 'email',
		);

		if ( $input['passwordless_enabled'] && $input['passwordless_enabled'] != $old_options['passwordless_enabled'] ) {

			foreach ( $passwordless_connections as $alias => $name ) {
				if ( strpos( $input['passwordless_method'], $alias ) !== false ) {
					$check_if_enabled[] = $name;
				}
			}
		} elseif ( $input['passwordless_method'] != $old_options['passwordless_method'] ) {

			foreach ( $passwordless_connections as $name ) {
				if ( strpos( $input['passwordless_method'], $name ) !== false ) {
					$check_if_enabled[] = $name;
				}
			}
		}

		if ( ! empty( $check_if_enabled ) ) {

			$response            = WP_Auth0_Api_Client::search_connection( $input['domain'], $input['auth0_app_token'] );
			$enabled_connections = array();
			foreach ( $response as $connection ) {
				if ( in_array( $input['client_id'], $connection->enabled_clients ) ) {
					$enabled_connections[] = $connection->strategy;
				}
			}

			$matching = array_intersect( $enabled_connections, $check_if_enabled );

			if ( array_diff( $matching, $check_if_enabled ) !== array_diff( $check_if_enabled, $matching ) ) {
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
					$error                              = __( "The 'Login redirect URL' cannot point to a foreign page.", 'wp-auth0' );
					$this->add_validation_error( $error );
				}
			}

			if ( strpos( $input['default_login_redirection'], 'action=logout' ) !== false ) {
				$input['default_login_redirection'] = $home_url;

				$error = __( "The 'Login redirect URL' cannot point to the logout page. ", 'wp-auth0' );
				$this->add_validation_error( $error );
			}
		}
		return $input;
	}
}
