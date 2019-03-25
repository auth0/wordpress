<?php

class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

	/**
	 * @deprecated - 3.6.0, use $this->_description instead
	 */
	const ADVANCED_DESCRIPTION = '';

	const ROTATE_TOKEN_NONCE_ACTION = 'auth0_rotate_migration_token';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
		'migration_ws_validation',
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
		add_action( 'wp_ajax_' . self::ROTATE_TOKEN_NONCE_ACTION, array( $this, self::ROTATE_TOKEN_NONCE_ACTION ) );

		$options = array(
			array(
				'name'     => __( 'Require Verified Email', 'wp-auth0' ),
				'opt'      => 'requires_verified_email',
				'id'       => 'wpa0_verified_email',
				'function' => 'render_verified_email',
			),
			array(
				'name'     => __( 'Skip Strategies', 'wp-auth0' ),
				'opt'      => 'skip_strategies',
				'id'       => 'wpa0_skip_strategies',
				'function' => 'render_skip_strategies',
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
				'name'     => __( 'Use Custom Lock JS URL', 'wp-auth0' ),
				'opt'      => 'custom_cdn_url',
				'id'       => 'wpa0_custom_cdn_url',
				'function' => 'render_custom_cdn_url',
			),
			array(
				'name'     => __( 'Custom Lock JS URL', 'wp-auth0' ),
				'opt'      => 'cdn_url',
				'id'       => 'wpa0_cdn_url',
				'function' => 'render_cdn_url',
			),
		);

		// TODO: Remove this once feature has been removed
		if ( $this->options->get( 'link_auth0_users' ) ) {
			$options[] = array(
				'name'     => __( 'Link Users with Same Email', 'wp-auth0' ),
				'opt'      => 'link_auth0_users',
				'id'       => 'wpa0_link_auth0_users',
				'function' => 'render_link_auth0_users',
			);
		}

		$options = $options + array(
			( count( $options ) ) => array(
				'name'     => __( 'Auto Provisioning', 'wp-auth0' ),
				'opt'      => 'auto_provisioning',
				'id'       => 'wpa0_auto_provisioning',
				'function' => 'render_auto_provisioning',
			),
			array(
				'name'     => __( 'User Migration Endpoints', 'wp-auth0' ),
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
				'name'     => '',
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
				'name'     => __( 'Implicit Login Flow', 'wp-auth0' ),
				'opt'      => 'auth0_implicit_workflow',
				'id'       => 'wpa0_auth0_implicit_workflow',
				'function' => 'render_auth0_implicit_workflow',
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
				'name'     => __( 'Auth0 Server Domain', 'wp-auth0' ),
				'opt'      => 'auth0_server_domain',
				'id'       => 'wpa0_auth0_server_domain',
				'function' => 'render_auth0_server_domain',
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

	/**
	 * Render form field and description for the `requires_verified_email` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_verified_email( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_skip_strategies' );
		$this->render_field_description(
			__( 'Require new users to both provide and verify their email before logging in. ', 'wp-auth0' ) .
			__( 'An email address is verified manually by an email from Auth0 or automatically by the provider. ', 'wp-auth0' ) .
			__( 'This will disallow logins from social connections that do not provide email (like Twitter)', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `skip_strategies` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @since 3.8.0
	 */
	public function render_skip_strategies( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', 'e.g. "twitter,ldap"' );
		$this->render_field_description(
			__( 'Enter one or more strategies, separated by commas, to skip email verification. ', 'wp-auth0' ) .
			__( 'You can find the strategy under the "Connection Name" field in the Auth0 dashboard. ', 'wp-auth0' ) .
			__( 'Leave this field blank to require email for all strategies. ', 'wp-auth0' ) .
			__( 'This could introduce a security risk and should be used sparingly, if at all', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `remember_users_session` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_remember_users_session( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'A user session by default is kept for two days. ', 'wp-auth0' ) .
			__( 'Enabling this setting will extend that and make the session be kept for 14 days', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `default_login_redirection` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_default_login_redirection( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'URL where successfully logged-in users are redirected when using the wp-login.php page. ', 'wp-auth0' ) .
			__( 'This can be overridden with the <code>redirect_to</code> URL parameter', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `lock_connections` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
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

	/**
	 * Render form field and description for the `force_https_callback` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_force_https_callback( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Forces the plugin to use HTTPS for the callback URL when a site supports both; ', 'wp-auth0' ) .
			__( 'if disabled, the protocol from the WordPress home URL will be used', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `custom_cdn_url` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_custom_cdn_url( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_cdn_url' );
		$this->render_field_description( __( 'Use a custom Lock CDN URL instead of the default. ', 'wp-auth0' ) );

		if ( ! $this->options->get( $args['opt_name'] ) ) {
			$this->render_field_description(
				__( 'Currently using:', 'wp-auth0' ) .
				' <code>' . WPA0_LOCK_CDN_URL . '</code>'
			);
		}
	}

	/**
	 * Render form field and description for the `cdn_url` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_cdn_url( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'url' );
		$this->render_field_description(
			__( 'This should point to the latest Lock JS available in the CDN and rarely needs to change', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `link_auth0_users` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * TODO: Deprecate
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @codeCoverageIgnore - To be deprecated
	 */
	public function render_link_auth0_users( $args = array() ) {
		$this->render_field_description(
			__( 'This feature may currently be active. ', 'wp-auth0' ) .
			__( 'Manage it with the "Account-Linking-Do-Not-Rename" Rule in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'rules' )
		);
	}

	/**
	 * Render form field and description for the `auto_provisioning` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_provisioning( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Create new users in the WordPress database when signups are off. ', 'wp-auth0' ) .
			__( 'Signups will not be allowed but successful Auth0 logins will add the user in WordPress', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `migration_ws` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_migration_ws( $args = array() ) {
		$value = $this->options->get( $args['opt_name'] );
		$this->render_switch( $args['label_for'], $args['opt_name'] );

		if ( $value ) {
			$this->render_field_description(
				__( 'User migration endpoints activated. ', 'wp-auth0' ) .
				__( 'See below for the token to use. ', 'wp-auth0' ) .
				__( 'The custom database scripts need to be configured manually as described ', 'wp-auth0' ) .
				$this->get_docs_link( 'cms/wordpress/user-migration' )
			);
			$this->render_field_description( 'Migration token:' );
			if ( $this->options->has_constant_val( 'migration_token' ) ) {
				$this->render_const_notice( 'migration_token' );
			}

			$migration_token = $this->options->get( 'migration_token' );
			printf(
				'<code class="code-block" id="auth0_migration_token" disabled>%s</code><br>',
				$migration_token ? sanitize_text_field( $migration_token ) : __( 'No migration token', 'wp-auth0' )
			);

			if ( ! $this->options->has_constant_val( 'migration_token' ) ) {
				printf(
					'<button id="%s" class="button button-secondary" data-confirm-msg="%s">%s</button>',
					esc_attr( self::ROTATE_TOKEN_NONCE_ACTION ),
					esc_attr(
						__( 'This will change your migration token immediately. ', 'wp-auth0' ) .
						__( 'The new token must be changed in the custom scripts for your database Connection. ', 'wp-auth0' ) .
						__( 'Continue?', 'wp-auth0' )
					),
					__( 'Generate New Migration Token', 'wp-auth0' )
				);
			}
		} else {
			$this->render_field_description(
				__( 'User migration endpoints deactivated. ', 'wp-auth0' ) .
				__( 'Custom database connections can be deactivated in the ', 'wp-auth0' ) .
				$this->get_dashboard_link( 'connections/database' )
			);
		}
	}

	/**
	 * Render form field and description for the `migration_ips_filter` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_migration_ws_ips_filter( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_migration_ws_ips' );
	}

	/**
	 * Render form field and description for the `migration_ips` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_migration_ws_ips( $args = array() ) {
		$ip_check = new WP_Auth0_Ip_Check( WP_Auth0_Options::Instance() );
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Only requests from these IPs will be allowed to access the migration endpoints. ', 'wp-auth0' ) .
			__( 'Separate multiple IPs with commas. ', 'wp-auth0' ) .
			__( 'The following Auth0 IPs are automatically whitelisted: ', 'wp-auth0' ) .
			'<br><br><code>' . $ip_check->get_ips_by_domain( null, '</code> <code>' ) . '</code>'
		);
	}

	/**
	 * Refer to the Features tab for the `auto_login` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 */
	public function render_auto_login() {
		$this->render_field_description(
			__( 'Please see the "Universal Login Page" setting on the Features tab', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `auth0_implicit_workflow` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
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

	/**
	 * Render form field and description for the `valid_proxy_ip` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_valid_proxy_ip( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Whitelist for proxy and load balancer IPs to enable logins and migration webservices', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `extra_conf` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_extra_conf( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Valid JSON for Lock options configuration; will override all options set elsewhere. ', 'wp-auth0' ) .
			$this->get_docs_link( 'libraries/lock/customization', 'See options and examples' )
		);
	}

	/**
	 * Render form field and description for the `custom_signup_fields` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_custom_signup_fields( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Valid array of JSON objects for additional signup fields in the Auth0 signup form. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'libraries/lock/v11/configuration#additionalsignupfields-array-',
				__( 'More information and examples', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `auth0_server_domain` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auth0_server_domain( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'The Auth0 domain used by the setup wizard to fetch your account information', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `jwt_auth_integration` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated 3.10.0, plugin is deprecated and removed from the WP plugin repo.
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_jwt_auth_integration( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'This setting is deprecated and will be removed in the next major version. ', 'wp-auth0' ) .
			__( 'This will enable the JWT Auth Users Repository override', 'wp-auth0' )
		);
	}

	public function auth0_rotate_migration_token() {
		check_ajax_referer( self::ROTATE_TOKEN_NONCE_ACTION );
		$this->options->set( 'migration_token', $this->generate_token() );
		wp_send_json_success();
	}

	public function basic_validation( $old_options, $input ) {
		$input['requires_verified_email'] = intval( ! empty( $input['requires_verified_email'] ) );

		$input['skip_strategies'] = isset( $input['skip_strategies'] ) ?
			sanitize_text_field( trim( $input['skip_strategies'] ) ) : '';

		$input['auto_provisioning']       = ( isset( $input['auto_provisioning'] ) ? $input['auto_provisioning'] : 0 );
		$input['remember_users_session']  = ( isset( $input['remember_users_session'] ) ? $input['remember_users_session'] : 0 ) == 1;
		$input['passwordless_enabled']    = ( isset( $input['passwordless_enabled'] ) ? $input['passwordless_enabled'] : 0 ) == 1;
		$input['jwt_auth_integration']    = ( isset( $input['jwt_auth_integration'] ) ? $input['jwt_auth_integration'] : 0 );
		$input['auth0_implicit_workflow'] = ( isset( $input['auth0_implicit_workflow'] ) ? $input['auth0_implicit_workflow'] : 0 );
		$input['force_https_callback']    = ( isset( $input['force_https_callback'] ) ? $input['force_https_callback'] : 0 );

		$input['custom_cdn_url'] = empty( $input['custom_cdn_url'] ) ? 0 : 1;

		$input['cdn_url'] = empty( $input['cdn_url'] ) ? WPA0_LOCK_CDN_URL : sanitize_text_field( $input['cdn_url'] );

		// If an invalid URL is used, default to previously saved (if there is one) or default URL.
		if ( ! filter_var( $input['cdn_url'], FILTER_VALIDATE_URL ) ) {
			$input['cdn_url'] = isset( $old_options['cdn_url'] ) ? $old_options['cdn_url'] : WPA0_LOCK_CDN_URL;
			self::add_validation_error( __( 'The Lock JS CDN URL used is not a valid URL.', 'wp-auth0' ) );
		}

		$input['social_twitter_key'] = isset( $input['social_twitter_key'] ) ?
			sanitize_text_field( $input['social_twitter_key'] ) : '';

		$input['social_twitter_secret'] = isset( $input['social_twitter_secret'] ) ?
			sanitize_text_field( $input['social_twitter_secret'] ) : '';

		$input['social_facebook_key'] = isset( $input['social_facebook_key'] ) ?
			sanitize_text_field( $input['social_facebook_key'] ) : '';

		$input['social_facebook_secret'] = isset( $input['social_facebook_secret'] ) ?
			sanitize_text_field( $input['social_facebook_secret'] ) : '';

		$input['migration_ips_filter'] = ( ! empty( $input['migration_ips_filter'] ) ? 1 : 0 );

		$input['migration_ips'] = isset( $input['migration_ips'] ) ?
			sanitize_text_field( $input['migration_ips'] ) : '';

		$input['valid_proxy_ip'] = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );

		$input['lock_connections'] = isset( $input['lock_connections'] ) ?
			trim( $input['lock_connections'] ) : '';

		$input['custom_signup_fields'] = isset( $input['custom_signup_fields'] ) ?
			trim( $input['custom_signup_fields'] ) : '';

		$input['extra_conf'] = isset( $input['extra_conf'] ) ? trim( $input['extra_conf'] ) : '';
		if ( ! empty( $input['extra_conf'] ) ) {
			if ( json_decode( $input['extra_conf'] ) === null ) {
				$error = __( 'The Extra settings parameter should be a valid json object', 'wp-auth0' );
				self::add_validation_error( $error );
			}
		}

		return $input;
	}

	/**
	 * Validation for the migration_ws setting.
	 * Generates new migration tokens if none is present.
	 *
	 * @param array $old_options - Option values before savings.
	 * @param array $input - New option values to validate.
	 *
	 * @return array
	 */
	public function migration_ws_validation( array $old_options, array $input ) {
		$input['migration_ws']    = (int) ! empty( $input['migration_ws'] );
		$input['migration_token'] = $this->options->get( 'migration_token' );

		// Migration endpoints or turned off, nothing to do.
		if ( empty( $input['migration_ws'] ) ) {
			return $input;
		}

		$input['migration_token_id'] = null;
		$this->router->setup_rewrites();
		flush_rewrite_rules();

		// If we don't have a token yet, generate one.
		if ( empty( $input['migration_token'] ) ) {
			$input['migration_token'] = $this->generate_token();
			return $input;
		}

		// If we do have a token, try to decode and store the JTI.
		$secret = $input['client_secret'];
		if ( ! empty( $input['client_secret_b64_encoded'] ) ) {
			$secret = base64_decode( $input['client_secret'] );
		}

		try {
			$token_decoded               = JWT::decode( $input['migration_token'], $secret, array( 'HS256' ) );
			$input['migration_token_id'] = isset( $token_decoded->jti ) ? $token_decoded->jti : null;
		} catch ( Exception $e ) {
			// If the JWT cannot be decoded then we use the token as-is without storing the JTI.
		}

		return $input;
	}

	/**
	 * TODO: Deprecate
	 *
	 * @codeCoverageIgnore - To be deprecated
	 */
	public function link_accounts_validation( $old_options, $input ) {
		$link_script = WP_Auth0_RulesLib::$link_accounts['script'];
		$link_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $link_script );
		$link_script = str_replace( 'REPLACE_WITH_YOUR_DOMAIN', $input['domain'], $link_script );
		$link_script = str_replace( 'REPLACE_WITH_YOUR_API_TOKEN', $input['auth0_app_token'], $link_script );
		return $this->rule_validation( $old_options, $input, 'link_auth0_users', WP_Auth0_RulesLib::$link_accounts['name'] . '-' . get_auth0_curatedBlogName(), $link_script );
	}

	/**
	 * Validate the URL used to redirect users after a successful login.
	 *
	 * @param array $old_options - Previously-saved options.
	 * @param array $input - Options to save.
	 *
	 * @return array
	 */
	public function loginredirection_validation( $old_options, $input ) {
		$new_redirect_url = esc_url_raw( strtolower( $input['default_login_redirection'] ) );
		$old_redirect_url = strtolower( $old_options['default_login_redirection'] );

		// No change so no validation needed.
		if ( $new_redirect_url === $old_redirect_url ) {
			return $input;
		}

		$home_url = home_url();

		// Set the default redirection URL to be the homepage.
		if ( empty( $new_redirect_url ) ) {
			$input['default_login_redirection'] = $home_url;
			return $input;
		}

		// Allow subdomains within the same domain.
		$home_domain     = $this->get_domain( $home_url );
		$redirect_domain = $this->get_domain( $new_redirect_url );
		if ( $home_domain === $redirect_domain ) {
			return $input;
		}

		// If we get here, the redirect URL is a page outside of the WordPress install.
		$error = __( 'Advanced > "Login Redirection URL" cannot point to another site.', 'wp-auth0' );
		$this->add_validation_error( $error );

		// Either revert to the previous (validated) value or set as the homepage.
		$input['default_login_redirection'] = ! empty( $old_options['default_login_redirection'] ) ?
			$old_options['default_login_redirection'] :
			$home_url;

		return $input;
	}

	private function get_domain( $url ) {
		$host_pieces = explode( '.', wp_parse_url( $url, PHP_URL_HOST ) );
		$domain      = array_pop( $host_pieces );
		if ( count( $host_pieces ) ) {
			$domain = array_pop( $host_pieces ) . '.' . $domain;
		}
		return $domain;
	}

	private function generate_token() {
		return JWT::urlsafeB64Encode( openssl_random_pseudo_bytes( 64 ) );
	}

	/*
	 * DEPRECATED
	 */

	/**
	 * Render form field and description for the `ip_range_check` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.9.0, unused
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_ip_range_check( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_ip_ranges' );
	}

	/**
	 * Render form field and description for the `ip_ranges` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.9.0, unused
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_ip_ranges( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Only one range per line! Range format should be as follows (spaces ignored): ', 'wp-auth0' ) .
			__( '<br><code>xx.xx.xx.xx - yy.yy.yy.yy</code>', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `social_twitter_key` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.9.0, functionality removed
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_social_twitter_key( $args = array() ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Twitter app key for the Social Amplification Widget. ', 'wp-auth0' ) .
			__( 'The app used here needs to have "read" and "write" permissions. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/twitter#2-get-your-consumer-key-and-consumer-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `social_twitter_secret` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.9.0, functionality removed
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_social_twitter_secret( $args = array() ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Secret for the app above. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/twitter#2-get-your-consumer-key-and-consumer-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `social_facebook_key` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.9.0, functionality removed
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_social_facebook_key( $args = array() ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Facebook app key for the Social Amplification Widget. ', 'wp-auth0' ) .
			__( 'The app used here needs to have "publish_actions" permission. ', 'wp-auth0' ) .
			__( 'Used for the Social Amplification Widget. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/facebook#5-get-your-app-id-and-app-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `social_facebook_secret` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @deprecated - 3.9.0, functionality removed
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_social_facebook_secret( $args = array() ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Secret for the app above. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/social/facebook#5-get-your-app-id-and-app-secret',
				__( 'Instructions here', 'wp-auth0' )
			)
		);
	}

	/**
	 * @deprecated - 3.6.0, handled by WP_Auth0_Admin_Features::render_passwordless_enabled()
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_passwordless_enabled() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}

	/**
	 * @deprecated - 3.6.0, passwordless method is determined by activating them for this Application.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_passwordless_method() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}

	/**
	 * @deprecated - 3.6.0, this feature was removed so this option is unused.
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_metrics() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}

	/**
	 * @deprecated - 3.6.0, handled by WP_Auth0_Admin_Generic::render_description().
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function render_advanced_description() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		printf( '<p class="a0-step-text">%s</p>', $this->_description );
	}

	/**
	 * Validate the `passwordless_method` option.
	 *
	 * @deprecated - 3.6.0, the `passwordless_method` option was removed in this version.
	 *
	 * @param array $old_options - previous option values.
	 * @param array $input - option values to be updated.
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function connections_validation( $old_options, $input ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $input;
	}
}
