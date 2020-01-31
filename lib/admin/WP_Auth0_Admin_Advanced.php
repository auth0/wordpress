<?php
/**
 * Contains class WP_Auth0_Admin_Advanced.
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_Admin_Advanced.
 * All setting fields and validations for wp-admin > Auth0 > Settings > Advanced tab.
 */
class WP_Auth0_Admin_Advanced extends WP_Auth0_Admin_Generic {

	/**
	 * AJAX nonce action for the rotate token endpoint.
	 *
	 * @see wp_auth0_ajax_rotate_migration_token()
	 */
	const ROTATE_TOKEN_NONCE_ACTION = 'auth0_rotate_migration_token';

	/**
	 * WP_Auth0_Routes instance.
	 *
	 * @var WP_Auth0_Routes
	 */
	protected $router;

	/**
	 * WP_Auth0_Admin_Advanced constructor.
	 *
	 * @param WP_Auth0_Options $options - WP_Auth0_Options instance.
	 * @param WP_Auth0_Routes  $router - WP_Auth0_Routes instance.
	 */
	public function __construct( WP_Auth0_Options $options, WP_Auth0_Routes $router ) {
		parent::__construct( $options );
		$this->router                = $router;
		$this->actions_middlewares[] = 'migration_ws_validation';
		$this->actions_middlewares[] = 'migration_ips_validation';
		$this->actions_middlewares[] = 'loginredirection_validation';
	}

	/**
	 * All settings in the Advanced tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = [
			[
				'name'     => __( 'Require Verified Email', 'wp-auth0' ),
				'opt'      => 'requires_verified_email',
				'id'       => 'wpa0_verified_email',
				'function' => 'render_verified_email',
			],
			[
				'name'     => __( 'Skip Strategies', 'wp-auth0' ),
				'opt'      => 'skip_strategies',
				'id'       => 'wpa0_skip_strategies',
				'function' => 'render_skip_strategies',
			],
			[
				'name'     => __( 'Remember User Session', 'wp-auth0' ),
				'opt'      => 'remember_users_session',
				'id'       => 'wpa0_remember_users_session',
				'function' => 'render_remember_users_session',
			],
			[
				'name'     => __( 'Login Redirection URL', 'wp-auth0' ),
				'opt'      => 'default_login_redirection',
				'id'       => 'wpa0_default_login_redirection',
				'function' => 'render_default_login_redirection',
			],
			[
				'name'     => __( 'Force HTTPS Callback', 'wp-auth0' ),
				'opt'      => 'force_https_callback',
				'id'       => 'wpa0_force_https_callback',
				'function' => 'render_force_https_callback',
			],
			[
				'name'     => __( 'Auto Provisioning', 'wp-auth0' ),
				'opt'      => 'auto_provisioning',
				'id'       => 'wpa0_auto_provisioning',
				'function' => 'render_auto_provisioning',
			],
			[
				'name'     => __( 'User Migration Endpoints', 'wp-auth0' ),
				'opt'      => 'migration_ws',
				'id'       => 'wpa0_migration_ws',
				'function' => 'render_migration_ws',
			],
			[
				'name'     => __( 'Migration IPs Whitelist', 'wp-auth0' ),
				'opt'      => 'migration_ips_filter',
				'id'       => 'wpa0_migration_ws_ips_filter',
				'function' => 'render_migration_ws_ips_filter',
			],
			[
				'name'     => '',
				'opt'      => 'migration_ips',
				'id'       => 'wpa0_migration_ws_ips',
				'function' => 'render_migration_ws_ips',
			],
			[
				'name'     => __( 'Valid Proxy IP', 'wp-auth0' ),
				'opt'      => 'valid_proxy_ip',
				'id'       => 'wpa0_valid_proxy_ip',
				'function' => 'render_valid_proxy_ip',
			],
			[
				'name'     => __( 'Auth0 Server Domain', 'wp-auth0' ),
				'opt'      => 'auth0_server_domain',
				'id'       => 'wpa0_auth0_server_domain',
				'function' => 'render_auth0_server_domain',
			],
		];

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
	public function render_verified_email( $args = [] ) {
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
	public function render_skip_strategies( $args = [] ) {
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
	public function render_remember_users_session( $args = [] ) {
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
	public function render_default_login_redirection( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'URL where successfully logged-in users are redirected when using the wp-login.php page. ', 'wp-auth0' ) .
			__( 'This can be overridden with the <code>redirect_to</code> URL parameter', 'wp-auth0' )
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
	public function render_force_https_callback( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Forces the plugin to use HTTPS for the callback URL when a site supports both; ', 'wp-auth0' ) .
			__( 'if disabled, the protocol from the WordPress home URL will be used', 'wp-auth0' )
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
	public function render_auto_provisioning( $args = [] ) {
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
	public function render_migration_ws( $args = [] ) {
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
	public function render_migration_ws_ips_filter( $args = [] ) {
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
	public function render_migration_ws_ips( $args = [] ) {
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
	 * Render form field and description for the `valid_proxy_ip` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_valid_proxy_ip( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Whitelist for proxy and load balancer IPs to enable logins and migration webservices', 'wp-auth0' )
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
	public function render_auth0_server_domain( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'The Auth0 domain used by the setup wizard to fetch your account information', 'wp-auth0' )
		);
	}

	/**
	 * Validate all settings without a specific validation method.
	 *
	 * @param array $old_options - Option values before savings.
	 * @param array $input - New option values to validate.
	 *
	 * @return array
	 */
	public function basic_validation( array $old_options, array $input ) {
		$input['requires_verified_email'] = $this->sanitize_switch_val( $input['requires_verified_email'] ?? null );
		$input['skip_strategies']         = $this->sanitize_text_val( $input['skip_strategies'] ?? null );
		$input['remember_users_session']  = $this->sanitize_switch_val( $input['remember_users_session'] ?? null );
		// `default_login_redirection` is sanitized in $this->loginredirection_validation() below.
		$input['force_https_callback'] = $this->sanitize_switch_val( $input['force_https_callback'] ?? null );
		$input['auto_provisioning']    = $this->sanitize_switch_val( $input['auto_provisioning'] ?? null );
		// `migration_ws` is sanitized in $this->migration_ws_validation() below.
		// `migration_token` is sanitized in $this->migration_ws_validation() below.
		$input['migration_ips_filter'] = $this->sanitize_switch_val( $input['migration_ips_filter'] ?? null );
		// `migration_ips` is sanitized in $this->migration_ips_validation() below.
		$input['valid_proxy_ip']      = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );
		$input['auth0_server_domain'] = $this->sanitize_text_val( $input['auth0_server_domain'] ?? null );
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
		$input['migration_ws']    = $this->sanitize_switch_val( $input['migration_ws'] ?? null );
		$input['migration_token'] = $this->options->get( 'migration_token' );

		// Migration endpoints or turned off, nothing to do.
		if ( ! $input['migration_ws'] ) {
			return $input;
		}

		$input['migration_token_id'] = null;
		$this->router->setup_rewrites();
		flush_rewrite_rules();

		// If we don't have a token yet, generate one.
		if ( empty( $input['migration_token'] ) ) {
			$input['migration_token'] = wp_auth0_generate_token();
			return $input;
		}

		// If we do have a token, try to decode and store the JTI.
		$secret = $input['client_secret'];

		try {
			$signature_verifier          = new WP_Auth0_SymmetricVerifier( $secret );
			$token_decoded               = $signature_verifier->verifyAndDecode( $input['migration_token'] );
			$input['migration_token_id'] = $token_decoded->getClaim( 'jti' );

			// phpcs:ignore
		} catch ( Exception $e ) {
			// If the JWT cannot be decoded then we use the token as-is without storing the JTI.
		}

		return $input;
	}

	/**
	 * Validation for the migration_ips setting.
	 * Generates new migration tokens if none is present.
	 *
	 * @param array $old_options - Option values before savings.
	 * @param array $input - New option values to validate.
	 *
	 * @return array
	 */
	public function migration_ips_validation( array $old_options, array $input ) {

		if ( empty( $input['migration_ips'] ) ) {
			$input['migration_ips'] = '';
			return $input;
		}

		$ip_addresses = explode( ',', $this->sanitize_text_val( $input['migration_ips'] ) );
		$ip_addresses = array_map( 'trim', $ip_addresses );
		$ip_addresses = array_filter( $ip_addresses );
		$ip_addresses = array_unique( $ip_addresses );

		if ( ! empty( $input['domain'] ) ) {
			$ip_check      = new WP_Auth0_Ip_Check();
			$whitelist_ips = $ip_check->get_ips_by_domain( $input['domain'], null );
			$ip_addresses  = array_diff( $ip_addresses, $whitelist_ips );
		}

		$input['migration_ips'] = implode( ', ', $ip_addresses );
		return $input;
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

	/**
	 * Get the top-level domain for a URL.
	 *
	 * @param string $url - Valid URL to parse.
	 *
	 * @return mixed|string
	 */
	private function get_domain( $url ) {
		$host_pieces = explode( '.', wp_parse_url( $url, PHP_URL_HOST ) );
		$domain      = array_pop( $host_pieces );
		if ( count( $host_pieces ) ) {
			$domain = array_pop( $host_pieces ) . '.' . $domain;
		}
		return $domain;
	}
}
