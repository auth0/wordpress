<?php
/**
 * Contains WP_Auth0_Admin_Basic.
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_Admin_Basic.
 * Fields and validations for the Basic settings tab.
 */
class WP_Auth0_Admin_Basic extends WP_Auth0_Admin_Generic {

	/**
	 * WP_Auth0_Admin_Basic constructor.
	 *
	 * @param WP_Auth0_Options $options - Instance of the WP_Auth0_Options class.
	 */
	public function __construct( WP_Auth0_Options $options ) {
		parent::__construct( $options );
		$this->_description          = __( 'Basic settings related to the Auth0 integration.', 'wp-auth0' );
		$this->actions_middlewares[] = 'wle_validation';
	}

	/**
	 * All settings in the Basic tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = [
			[
				'name'     => __( 'Domain', 'wp-auth0' ),
				'opt'      => 'domain',
				'id'       => 'wpa0_domain',
				'function' => 'render_domain',
			],
			[
				'name'     => __( 'Custom Domain', 'wp-auth0' ),
				'opt'      => 'custom_domain',
				'id'       => 'wpa0_custom_domain',
				'function' => 'render_custom_domain',
			],
			[
				'name'     => __( 'Client ID', 'wp-auth0' ),
				'opt'      => 'client_id',
				'id'       => 'wpa0_client_id',
				'function' => 'render_client_id',
			],
			[
				'name'     => __( 'Client Secret', 'wp-auth0' ),
				'opt'      => 'client_secret',
				'id'       => 'wpa0_client_secret',
				'function' => 'render_client_secret',
			],
			[
				'name'     => __( 'JWT Signature Algorithm', 'wp-auth0' ),
				'opt'      => 'client_signing_algorithm',
				'id'       => 'wpa0_client_signing_algorithm',
				'function' => 'render_client_signing_algorithm',
			],
			[
				'name'     => __( 'JWKS Cache Time (in minutes)', 'wp-auth0' ),
				'opt'      => 'cache_expiration',
				'id'       => 'wpa0_cache_expiration',
				'function' => 'render_cache_expiration',
			],
			[
				'name'     => __( 'Original Login Form on wp-login.php', 'wp-auth0' ),
				'opt'      => 'wordpress_login_enabled',
				'id'       => 'wpa0_login_enabled',
				'function' => 'render_allow_wordpress_login',
			],
			[
				'name'     => __( 'Allow Signups', 'wp-auth0' ),
				'id'       => 'wpa0_allow_signup',
				'function' => 'render_allow_signup',
			],
		];
		$this->init_option_section( '', 'basic', $options );
	}

	/**
	 * Render form field and description for the `domain` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_domain( $args = [] ) {
		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', 'your-tenant.auth0.com', $style );
		$this->render_field_description(
			__( 'Auth0 Domain, found in your Application settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'applications' )
		);
	}

	/**
	 * Render form field and description for the `custom_domain` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @since 3.7.0
	 */
	public function render_custom_domain( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', 'login.yourdomain.com' );
		$this->render_field_description(
			__( 'Custom login domain. ', 'wp-auth0' ) .
			$this->get_docs_link( 'custom-domains', __( 'More information here', 'wp-auth0' ) )
		);
	}

	/**
	 * Render form field and description for the `client_id` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_client_id( $args = [] ) {
		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', '', $style );
		$this->render_field_description(
			__( 'Client ID, found in your Application settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'applications' )
		);
	}

	/**
	 * Render form field and description for the `client_secret` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_client_secret( $args = [] ) {
		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'password', '', $style );
		$this->render_field_description(
			__( 'Client Secret, found in your Application settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'applications' )
		);
	}

	/**
	 * Render form field and description for the `client_signing_algorithm` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_client_signing_algorithm( $args = [] ) {
		$curr_value = $this->options->get( $args['opt_name'] ) ?: WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG;
		$this->render_radio_buttons(
			[ 'HS256', 'RS256' ],
			$args['label_for'],
			$args['opt_name'],
			$curr_value
		);
		$this->render_field_description(
			__( 'This value can be found the Application settings in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'applications' ) .
			__( ' under Show Advanced Settings > OAuth > "JsonWebToken Signature Algorithm"', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `cache_expiration` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_cache_expiration( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'number' );
		printf(
			' <button id="auth0_delete_cache_transient" class="button button-secondary">%s</button>',
			__( 'Delete Cache', 'wp-auth0' )
		);
		$this->render_field_description( __( 'JWKS cache expiration in minutes (use 0 for no caching)', 'wp-auth0' ) );

		$domain = $this->options->get( 'domain' );
		if ( $domain ) {
			$this->render_field_description(
				sprintf(
					'<a href="https://%s/.well-known/jwks.json" target="_blank">%s</a>',
					$domain,
					__( 'View your JWKS here', 'wp-auth0' )
				)
			);
		}
	}

	/**
	 * Render form field and description for the `wordpress_login_enabled` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_allow_wordpress_login( $args = [] ) {

		$isset_desc = sprintf(
			'<code class="code-block"><a href="%s?wle" target="_blank">%s?wle</a></code>',
			wp_login_url(),
			wp_login_url()
		);

		$code_desc = '<code class="code-block">' . __( 'Save settings to generate URL.', 'wp-auth0' ) . '</code>';
		$wle_code  = $this->options->get( 'wle_code' );
		if ( $wle_code ) {
			$code_desc = str_replace( '?wle', '?wle=' . $wle_code, $isset_desc );
		}

		$buttons = [
			[
				'label' => __( 'Never', 'wp-auth0' ),
				'value' => 'no',
			],
			[
				'label' => __( 'Via a link under the Auth0 form', 'wp-auth0' ),
				'value' => 'link',
				'desc'  => __( 'URL is the same as below', 'wp-auth0' ),
			],
			[
				'label' => __( 'When "wle" query parameter is present', 'wp-auth0' ),
				'value' => 'isset',
				'desc'  => $isset_desc,
			],
			[
				'label' => __( 'When "wle" query parameter contains specific code', 'wp-auth0' ),
				'value' => 'code',
				'desc'  => $code_desc,
			],
		];

		printf(
			'<div class="subelement"><span class="description">%s.</span></div><br>',
			__( 'Logins and signups using the original form will NOT be pushed to Auth0', 'wp-auth0' )
		);

		$this->render_radio_buttons(
			$buttons,
			$args['label_for'],
			$args['opt_name'],
			$this->options->get( $args['opt_name'] ),
			true
		);
	}

	/**
	 * Render description for the `wpa0_allow_signup` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_allow_signup() {
		if ( is_multisite() ) {
			$settings_text = __(
				'"Allow new registrations" in the Network Admin > Settings > Network Settings',
				'wp-auth0'
			);
		} else {
			$settings_text = __( '"Anyone can register" in the WordPress General Settings', 'wp-auth0' );
		}
		$allow_signup = $this->options->is_wp_registration_enabled();
		$this->render_field_description(
			__( 'Signups are currently ', 'wp-auth0' ) . '<b>' .
			( $allow_signup ? __( 'enabled', 'wp-auth0' ) : __( 'disabled', 'wp-auth0' ) ) .
			'</b>' . __( ' by this setting ', 'wp-auth0' ) . $settings_text
		);
	}

	/**
	 * Validation for Basic settings tab.
	 *
	 * @param array $old_options - Options before saving the settings form.
	 * @param array $input - New options being saved.
	 *
	 * @return array
	 */
	public function basic_validation( $old_options, $input ) {

		if ( wp_cache_get( 'doing_db_update', WPA0_CACHE_GROUP ) ) {
			return $input;
		}

		$input['domain']           = sanitize_text_field( $input['domain'] );
		$input['custom_domain']    = sanitize_text_field( $input['custom_domain'] );
		$input['client_id']        = sanitize_text_field( $input['client_id'] );
		$input['cache_expiration'] = absint( $input['cache_expiration'] );

		$input['client_secret'] = sanitize_text_field( $input['client_secret'] );
		if ( __( '[REDACTED]', 'wp-auth0' ) === $input['client_secret'] ) {
			$input['client_secret'] = $old_options['client_secret'];
		}

		if ( ! in_array( $input['client_signing_algorithm'], [ 'HS256', 'RS256' ] ) ) {
			$input['client_signing_algorithm'] = WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG;
		}

		if ( empty( $input['domain'] ) ) {
			$this->add_validation_error( __( 'You need to specify a domain', 'wp-auth0' ) );
		}

		if ( empty( $input['client_id'] ) ) {
			$this->add_validation_error( __( 'You need to specify a Client ID', 'wp-auth0' ) );
		}

		if ( empty( $input['client_secret'] ) && empty( $old_options['client_secret'] ) ) {
			$this->add_validation_error( __( 'You need to specify a Client Secret', 'wp-auth0' ) );
		}

		return $input;
	}

	/**
	 * Validation for the WordPress Login Enabled setting.
	 *
	 * @param array $old_options - Previous option values.
	 * @param array $input - Option values being saved.
	 *
	 * @return mixed
	 */
	public function wle_validation( $old_options, $input ) {
		if ( ! in_array( $input['wordpress_login_enabled'], [ 'link', 'isset', 'code', 'no' ] ) ) {
			$input['wordpress_login_enabled'] = $this->options->get_default( 'wordpress_login_enabled' );
		}
		$input['wle_code'] = $this->options->get( 'wle_code' ) ?: str_shuffle( uniqid() . uniqid() );
		return $input;
	}
}
