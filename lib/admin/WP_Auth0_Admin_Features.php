<?php

class WP_Auth0_Admin_Features extends WP_Auth0_Admin_Generic {

	/**
	 *
	 * @deprecated 3.6.0 - Use $this->_description instead
	 */
	const FEATURES_DESCRIPTION = '';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
		'georule_validation',
		'sso_validation',
		'security_validation',
		'incomerule_validation',
		'fullcontact_validation',
		'mfa_validation',
	);

	/**
	 * WP_Auth0_Admin_Features constructor.
	 *
	 * @param WP_Auth0_Options_Generic $options
	 */
	public function __construct( WP_Auth0_Options_Generic $options ) {
		parent::__construct( $options );
		$this->_description = __( 'Settings related to specific features provided by the plugin.', 'wp-auth0' );
	}

	/**
	 * All settings in the Features tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = array(
			array(
				'name'     => __( 'Password Policy', 'wp-auth0' ),
				'opt'      => 'password_policy',
				'id'       => 'wpa0_password_policy',
				'function' => 'render_password_policy',
			),
			array(
				'name'     => __( 'Single Sign On (SSO)', 'wp-auth0' ),
				'opt'      => 'sso',
				'id'       => 'wpa0_sso',
				'function' => 'render_sso',
			),
			array(
				'name'     => __( 'Single Logout', 'wp-auth0' ),
				'opt'      => 'singlelogout',
				'id'       => 'wpa0_singlelogout',
				'function' => 'render_singlelogout',
			),
			array(
				'name'     => __( 'Passwordless Login', 'wp-auth0' ),
				'opt'      => 'passwordless_enabled',
				'id'       => 'wpa0_passwordless_enabled',
				'function' => 'render_passwordless_enabled',
			),
			array(
				'name'     => __( 'Universal Login Page', 'wp-auth0' ),
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
				'name'     => __( 'Multifactor Authentication (MFA)', 'wp-auth0' ),
				'opt'      => 'mfa',
				'id'       => 'wpa0_mfa',
				'function' => 'render_mfa',
			),
			array(
				'name'     => __( 'FullContact Integration', 'wp-auth0' ),
				'opt'      => 'fullcontact',
				'id'       => 'wpa0_fullcontact',
				'function' => 'render_fullcontact',
			),
			array(
				'name'     => __( 'FullContact API Key', 'wp-auth0' ),
				'opt'      => 'fullcontact_apikey',
				'id'       => 'wpa0_fullcontact_key',
				'function' => 'render_fullcontact_apikey',
			),
			array(
				'name'     => __( 'Store Geolocation', 'wp-auth0' ),
				'opt'      => 'geo_rule',
				'id'       => 'wpa0_geo',
				'function' => 'render_geo',
			),
			array(
				'name'     => __( 'Store Zipcode Income', 'wp-auth0' ),
				'opt'      => 'income_rule',
				'id'       => 'wpa0_income',
				'function' => 'render_income',
			),
			array(
				'name'     => __( 'Override WordPress Avatars', 'wp-auth0' ),
				'opt'      => 'override_wp_avatars',
				'id'       => 'wpa0_override_wp_avatars',
				'function' => 'render_override_wp_avatars',
			),
		);
		$this->init_option_section( '', 'features', $options );
	}

	/**
	 * Render form field and description for the `password_policy` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_password_policy( $args = array() ) {
		$this->render_radio_buttons(
			array(
				array(
					'label' => 'None',
					'value' => '',
				),
				'low',
				'fair',
				'good',
				'excellent',
			),
			$args['label_for'],
			$args['opt_name'],
			$this->options->get( $args['opt_name'], 'fair' )
		);
		$this->render_field_description(
			__( 'Password security policy for the database connection used by this application. ', 'wp-auth0' ) .
			__( 'Changing the policy here will change it for all other applications using this database. ', 'wp-auth0' ) .
			__( 'For information on policy levels, see our ', 'wp-auth0' ) .
			$this->get_docs_link(
				'connections/database/password-strength',
				__( 'help page on password strength', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `sso` option.
	 * If SSO is off, the SLO setting will be hidden and turned off as well.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_sso( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_singlelogout' );
		$this->render_field_description(
			__( 'SSO allows users to sign in once to multiple Applications in the same tenant. ', 'wp-auth0' ) .
			__( 'Turning this on will attempt to automatically log a user in when they visit wp-login.php. ', 'wp-auth0' ) .
			__( 'This setting will not affect how shortcodes and widgets work. ', 'wp-auth0' ) .
			__( 'For more information, see our ', 'wp-auth0' ) .
			$this->get_docs_link( 'sso/current/introduction', __( 'help page on SSO', 'wp-auth0' ) )
		);
	}

	/**
	 * Render form field and description for the `singlelogout` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_singlelogout( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Log users out of this site and all others connected to the tenant', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `passwordless_enabled` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_passwordless_enabled( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Turn on Passwordless login (email or SMS) in the Auth0 form. ', 'wp-auth0' ) .
			__( 'Passwordless connections are managed in the ', 'wp-auth0' ) .
			$this->get_dashboard_link( 'connections/passwordless' ) .
			__( ' and at least one must be active and enabled on this Application for this to work. ', 'wp-auth0' ) .
			__( 'Username/password login is not enabled when Passwordless is on', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `auto_login` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_login( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_auto_login_method' );
		$this->render_field_description(
			__( 'Use the Universal Login Page (ULP) for authentication. ', 'wp-auth0' ) .
			__( 'When turned on, <code>wp-login.php</code> will redirect to the hosted login page. ', 'wp-auth0' ) .
			__( 'When turned off, <code>wp-login.php</code> will show an embedded login form. ', 'wp-auth0' ) .
			$this->get_docs_link( 'guides/login/universal-vs-embedded', __( 'More on ULP vs embedded here', 'wp-auth0' ) )
		);
	}

	/**
	 * Render form field and description for the `auto_login_method` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_login_method( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Enter a name here to automatically use a single, specific connection to login . ', 'wp-auth0' ) .
			sprintf(
				__( 'Find the method name to use under Connections > [Connection Type] in your %s. ', 'wp-auth0' ),
				$this->get_dashboard_link()
			) .
			__( 'Click the expand icon and use the value in the "Name" field (like "google-oauth2")', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `mfa` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_mfa( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Mark this if you want to enable multifactor authentication with Auth0 Guardian. ', 'wp-auth0' ) .
			sprintf(
				__( 'You can enable other MFA providers in the %s. ', 'wp-auth0' ),
				$this->get_dashboard_link( 'multifactor' )
			) . __( 'For more information, see our ', 'wp-auth0' ) .
			$this->get_docs_link( 'multifactor-authentication', __( 'help page on MFA', 'wp-auth0' ) )
		);
	}

	/**
	 * Render form field and description for the `fullcontact` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_fullcontact( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_fullcontact_key' );
		$this->render_field_description(
			__( 'Enriches your user profiles with the data provided by FullContact. ', 'wp-auth0' ) .
			__( 'A valid FullContact API key is required for this to work. ', 'wp-auth0' ) .
			__( 'For more details, see our ', 'wp-auth0' ) .
			$this->get_docs_link(
				'monitoring/track-signups-enrich-user-profile-generate-leads',
				__( 'help page on tracking signups', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `fullcontact_apikey` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_fullcontact_apikey( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
	}

	/**
	 * Render form field and description for the `geo_rule` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_geo( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Mark this if you want to store geolocation data based on the IP of the user logging in', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `income_rule` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_income( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Mark this if you want to store projected income data based on the zipcode of the user\'s IP', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `override_wp_avatars` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_override_wp_avatars( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Overrides the WordPress avatar with the Auth0 profile avatar', 'wp-auth0' )
		);
	}

	public function basic_validation( $old_options, $input ) {
		// SLO will be turned off in WP_Auth0_Admin_Features::sso_validation() if SSO is not on.
		$input['singlelogout']        = ( isset( $input['singlelogout'] ) ? $input['singlelogout'] : 0 );
		$input['override_wp_avatars'] = ( isset( $input['override_wp_avatars'] ) ? $input['override_wp_avatars'] : 0 );

		return $input;
	}

	/**
	 * Update the Auth0 Application if SSO is turned on and disable SLO if it is turned off.
	 *
	 * @param array $old_options - option values before saving.
	 * @param array $input - new option values being saved.
	 *
	 * @return array
	 */
	public function sso_validation( $old_options, $input ) {
		$input['sso'] = ( isset( $input['sso'] ) ? $input['sso'] : 0 );
		$is_sso       = ! empty( $input['sso'] );

		// SLO does not function without SSO so turn off SLO if SSO is off.
		if ( ! $is_sso ) {
			unset( $input['singlelogout'] );
		}

		// If SSO is off or nothing was changed, exit early.
		if ( ! $is_sso || $old_options['sso'] === $input['sso'] ) {
			return $input;
		}

		$app_update_success = false;
		$app_token          = WP_Auth0_Api_Client::get_client_token();
		if ( $app_token ) {
			$update_result      = WP_Auth0_Api_Client::update_client(
				$input['domain'],
				$app_token,
				$input['client_id'],
				true
			);
			$app_update_success = (bool) $update_result;
		}
		if ( ! $app_update_success ) {
			$this->add_validation_error(
				__( 'The SSO setting for your Application could not be updated automatically. ', 'wp-auth0' ) .
				__( 'Check that "Use Auth0 instead of the IdP to do Single Sign On" is turned on in the ', 'wp-auth0' ) .
				$this->get_dashboard_link( 'applications/' . $input['client_id'] . '/settings' )
			);
		}

		return $input;
	}

	/**
	 * Update the password policy for the database connection used with this application
	 *
	 * @param array $old_options - previous option values
	 * @param array $input - new option values
	 *
	 * @return array
	 */
	public function security_validation( $old_options, $input ) {
		$input['password_policy'] = ! empty( $input['password_policy'] ) ? $input['password_policy'] : null;

		if ( $old_options['password_policy'] !== $input['password_policy'] ) {
			$domain      = $input['domain'];
			$app_token   = $input['auth0_app_token'];
			$connections = WP_Auth0_Api_Client::search_connection( $domain, $app_token, 'auth0' );

			if ( empty( $connections ) ) {
				$this->add_validation_error(
					__( 'No database connections found for this application. ', 'wp-auth0' ) .
					$this->get_dashboard_link( 'connections/database', __( 'See all database connections', 'wp-auth0' ) )
				);
			}

			foreach ( $connections as $connection ) {
				if ( in_array( $input['client_id'], $connection->enabled_clients ) ) {
					$patch       = array( 'options' => array( 'passwordPolicy' => $input['password_policy'] ) );
					$update_resp = WP_Auth0_Api_Client::update_connection( $domain, $app_token, $connection->id, $patch );

					if ( false === $update_resp ) {
						$this->add_validation_error(
							__( 'There was a problem updating the password policy. ', 'wp-auth0' ) .
							__( 'Please manually review and update the policy. ', 'wp-auth0' ) .
							$this->get_dashboard_link( 'connections/database', __( 'See all database connections', 'wp-auth0' ) )
						);
					}
				}
			}
		}
		return $input;
	}

	public function fullcontact_validation( $old_options, $input ) {
		$fullcontact_script = WP_Auth0_RulesLib::$fullcontact['script'];
		$fullcontact_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $fullcontact_script );
		$fullcontact_script = str_replace( 'REPLACE_WITH_YOUR_FULLCONTACT_API_KEY', $input['fullcontact_apikey'], $fullcontact_script );
		return $this->rule_validation( $old_options, $input, 'fullcontact', WP_Auth0_RulesLib::$fullcontact['name'] . '-' . get_auth0_curatedBlogName(), $fullcontact_script );
	}

	public function mfa_validation( $old_options, $input ) {

		if ( ! isset( $input['mfa'] ) ) {
			$input['mfa'] = null;
		}
		if ( ! isset( $old_options['mfa'] ) ) {
			$old_options['mfa'] = null;
		}

		if ( $old_options['mfa'] != $input['mfa'] && $input['mfa'] !== null ) {
			WP_Auth0_Api_Client::update_guardian( $input['domain'], $input['auth0_app_token'], 'push-notification', true );
		}

		$mfa_script = WP_Auth0_RulesLib::$guardian_MFA['script'];
		$mfa_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $mfa_script );
		return $this->rule_validation( $old_options, $input, 'mfa', WP_Auth0_RulesLib::$guardian_MFA['name'] . '-' . get_auth0_curatedBlogName(), $mfa_script );
	}

	public function georule_validation( $old_options, $input ) {
		$geo_script = WP_Auth0_RulesLib::$geo['script'];
		$geo_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $geo_script );
		return $this->rule_validation( $old_options, $input, 'geo_rule', WP_Auth0_RulesLib::$geo['name'] . '-' . get_auth0_curatedBlogName(), $geo_script );
	}

	public function incomerule_validation( $old_options, $input ) {
		$income_script = WP_Auth0_RulesLib::$income['script'];
		$income_script = str_replace( 'REPLACE_WITH_YOUR_CLIENT_ID', $input['client_id'], $income_script );
		return $this->rule_validation( $old_options, $input, 'income_rule', WP_Auth0_RulesLib::$income['name'] . '-' . get_auth0_curatedBlogName(), $income_script );
	}

	/**
	 *
	 * @deprecated 3.6.0 - Handled by WP_Auth0_Admin_Generic::render_description()
	 */
	public function render_features_description() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		printf( '<p class="a0-step-text">%s</p>', $this->_description );
	}
}
