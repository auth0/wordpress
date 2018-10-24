<?php

class WP_Auth0_Lock10_Options {

	const LOCK_GLOBAL_JS_VAR_NAME = 'wpAuth0LockGlobal';

	protected $wp_options;
	protected $extended_settings;
	protected $signup_mode = false;

	/**
	 * WP_Auth0_Lock10_Options constructor.
	 *
	 * @param array                 $extended_settings Argument in renderAuth0Form(), used by shortcode and widget.
	 * @param null|WP_Auth0_Options $opts WP_Auth0_Options instance.
	 */
	public function __construct( $extended_settings = array(), $opts = null ) {
		$this->wp_options        = ! empty( $opts ) ? $opts : WP_Auth0_Options::Instance();
		$this->extended_settings = $extended_settings;
	}

	public function get_code_callback_url() {
		return $this->wp_options->get_wp_auth0_url( $this->get_callback_protocol() );
	}

	public function get_implicit_callback_url() {
		return $this->wp_options->get_wp_auth0_url( $this->get_callback_protocol(), true );
	}

	public function get_sso() {
		return $this->_get_boolean( $this->wp_options->get( 'sso' ) );
	}

	public function get_client_id() {
		return $this->wp_options->get( 'client_id' );
	}

	public function get_domain() {
		return $this->wp_options->get_auth_domain();
	}

	public function get_auth0_implicit_workflow() {
		return $this->_get_boolean( $this->wp_options->get( 'auth0_implicit_workflow' ) );
	}

	public function is_registration_enabled() {
		return $this->wp_options->is_wp_registration_enabled();
	}

	public function show_as_modal() {
		return isset( $this->extended_settings['show_as_modal'] ) && $this->extended_settings['show_as_modal'];
	}

	public function get_state_obj( $redirect_to = null ) {

		$stateObj = array(
			'interim' => ( isset( $_GET['interim-login'] ) && $_GET['interim-login'] == 1 ),
			'nonce'   => WP_Auth0_State_Handler::get_instance()->get_unique(),
		);

		if ( ! empty( $redirect_to ) ) {
			$stateObj['redirect_to'] = addslashes( $redirect_to );
		} elseif ( isset( $_GET['redirect_to'] ) ) {
			$stateObj['redirect_to'] = addslashes( $_GET['redirect_to'] );
		}

		return base64_encode( json_encode( $stateObj ) );
	}

	protected function _get_boolean( $value ) {
		return 1 === (int) $value || strtolower( $value ) === 'true';
	}

	protected function _is_valid( $array, $key ) {
		return isset( $array[ $key ] ) && trim( $array[ $key ] ) !== '';
	}

	protected function build_settings( $settings ) {
		$options_obj = array();

		if ( isset( $settings['language'] ) && ! empty( $settings['language'] ) ) {
			$options_obj['language'] = $settings['language'];
		}
		if ( isset( $settings['language_dictionary'] ) && ! empty( $settings['language_dictionary'] ) ) {
			$options_obj['languageDictionary'] = json_decode( $settings['language_dictionary'], true );
		}

		if ( isset( $settings['form_title'] ) && trim( $settings['form_title'] ) !== '' ) {

			if ( ! isset( $options_obj['languageDictionary'] ) ) {
				$options_obj['languageDictionary'] = array();
			}

			$options_obj['languageDictionary']['title'] = $settings['form_title'];

		}

		if ( $this->_is_valid( $settings, 'social_big_buttons' ) ) {
			$options_obj['socialButtonStyle'] = $settings['social_big_buttons'] ? 'big' : 'small';
		}
		if ( isset( $settings['gravatar'] ) && empty( $settings['gravatar'] ) ) {
			$options_obj['avatar'] = null;
		}
		if ( $this->_is_valid( $settings, 'username_style' ) ) {
			$options_obj['usernameStyle'] = $settings['username_style'];
		}
		if ( $this->_is_valid( $settings, 'sso' ) ) {
			$options_obj['auth']['sso'] = $this->_get_boolean( $settings['sso'] );
		}

		if ( $this->_is_valid( $settings, 'icon_url' ) || $this->_is_valid( $settings, 'primary_color' ) ) {
			$options_obj['theme'] = array();
			if ( $this->_is_valid( $settings, 'icon_url' ) ) {
				$options_obj['theme']['logo'] = $settings['icon_url'];
			}
			if ( $this->_is_valid( $settings, 'primary_color' ) ) {
				$options_obj['theme']['primaryColor'] = $settings['primary_color'];
			}
		}
		if ( $this->_is_valid( $settings, 'lock_connections' ) ) {
			$options_obj['allowedConnections'] = $this->wp_options->get_lock_connections();
		}
		if ( isset( $settings['extra_conf'] ) && trim( $settings['extra_conf'] ) !== '' ) {
			$extra_conf_arr = json_decode( $settings['extra_conf'], true );
			$options_obj    = array_merge_recursive( $extra_conf_arr, $options_obj );
		}
		if ( $this->signup_mode ) {
			$options_obj['allowLogin'] = false;
		} elseif ( isset( $_GET['action'] ) && $_GET['action'] == 'register' ) {
			$options_obj['allowLogin'] = true;
		}
		return $options_obj;
	}

	public function get_sso_options() {
		$options['scope']        = WP_Auth0_LoginManager::get_userinfo_scope( 'sso' );
		$options['responseType'] = 'id_token';
		$options['redirectUri']  = $this->get_implicit_callback_url();
		$options['nonce']        = WP_Auth0_Nonce_Handler::get_instance()->get_unique();
		unset( $options['authParams'] );

		$redirect_to      = ! empty( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : null;
		$options['state'] = $this->get_state_obj( $redirect_to );

		return $options;
	}

	public function get_lock_options() {
		$extended_settings = $this->extended_settings;
		if ( isset( $extended_settings['show_as_modal'] ) ) {
			unset( $extended_settings['show_as_modal'] );
		}
		if ( isset( $extended_settings['modal_trigger_name'] ) ) {
			unset( $extended_settings['modal_trigger_name'] );
		}

		$redirect_to = null;
		if ( isset( $this->extended_settings['redirect_to'] ) ) {
			$redirect_to = $this->extended_settings['redirect_to'];
		}

		$extraOptions = array(
			'auth' => array(
				'params' => array(
					'state' => $this->get_state_obj( $redirect_to ),
					'scope' => WP_Auth0_LoginManager::get_userinfo_scope( 'lock' ),
				),
			),
		);

		if ( $this->get_auth0_implicit_workflow() ) {
			$extraOptions['auth']['responseType']    = 'id_token';
			$extraOptions['auth']['responseMode']    = 'form_post';
			$extraOptions['auth']['redirectUrl']     = $this->get_implicit_callback_url();
			$extraOptions['autoParseHash']           = false;
			$extraOptions['auth']['params']['nonce'] = WP_Auth0_Nonce_Handler::get_instance()->get_unique();
		} else {
			$extraOptions['auth']['responseType'] = 'code';
			$extraOptions['auth']['redirectUrl']  = $this->get_code_callback_url();
		}

		if ( $this->wp_options->get( 'custom_domain' ) ) {
			$tenant_region                        = WP_Auth0::get_tenant_region( $this->wp_options->get( 'domain' ) );
			$extraOptions['configurationBaseUrl'] = sprintf(
				'https://cdn%s.auth0.com',
				( 'us' === $tenant_region ? '' : '.' . $tenant_region )
			);
		}

		$options_obj       = $this->build_settings( $this->wp_options->get_options() );
		$extended_settings = $this->build_settings( $extended_settings );

		$options_obj = array_replace_recursive( $extraOptions, $options_obj, $extended_settings );

		if ( ! $this->wp_options->is_wp_registration_enabled() && ! isset( $options_obj['allowSignUp'] ) ) {
			$options_obj['allowSignUp'] = false;
		}

		if ( ! $this->show_as_modal() ) {
			$options_obj['container'] = WPA0_AUTH0_LOGIN_FORM_ID;
		}

		if ( ! $this->is_registration_enabled() ) {
			$options_obj['disableSignupAction'] = true;
		}

		if ( function_exists( 'login_header' ) && isset( $_GET['action'] ) && 'register' === $_GET['action'] ) {
			$options_obj['initialScreen'] = 'signUp';
		}

		return $options_obj;
	}

	/**
	 * Get the protocol to use for callback URLs.
	 *
	 * @return null|string - Returns 'https' if forced, null (use site default) if not.
	 */
	private function get_callback_protocol() {
		return $this->_get_boolean( $this->wp_options->get( 'force_https_callback' ) ) ? 'https' : null;
	}

	/**
	 * @deprecated - 3.6.0, not used, determined in wp-content/plugins/auth0/assets/js/lock-init.js.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_lock_classname() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		if ( $this->_get_boolean( $this->wp_options->get( 'passwordless_enabled' ) ) ) {
			return 'Auth0LockPasswordless';
		} else {
			return 'Auth0Lock';
		}
	}

	/**
	 * @deprecated - 3.6.0, replaced with WP_Auth0_Options::Instance()->get( 'passwordless_enabled' ).
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function isPasswordlessEnable() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $this->_get_boolean( $this->wp_options->get( 'passwordless_enabled' ) );
	}

	/**
	 * @deprecated - 3.6.0, not used, invalid way to display Passwordless in Lock 11.2.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_lock_show_method() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return 'show';
	}

	/**
	 * @deprecated - 3.6.0, not used, use WP_Auth0_Options::Instance->get( 'custom_css' ) instead.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_custom_css() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $this->wp_options->get( 'custom_css' );
	}

	/**
	 * @deprecated - 3.6.0, not used, use WP_Auth0_Options::Instance->get( 'custom_js' ) instead.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_custom_js() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $this->wp_options->get( 'custom_js' );
	}

	/**
	 * @deprecated - 3.6.0, not used, call WP_Auth0::ready() instead.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function can_show() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return WP_Auth0::ready();
	}

	/**
	 * @deprecated - 3.6.0, not used, use WP_Auth0_Options::Instance->get( 'cdn_url' ) instead.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_cdn_url() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $this->wp_options->get( 'cdn_url' );
	}

	/**
	 * @deprecated - 3.6.0, not used, use (bool) WP_Auth0_Options::Instance->get( 'wordpress_login_enabled' ) instead.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_wordpress_login_enabled() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $this->_get_boolean( $this->wp_options->get( 'wordpress_login_enabled' ) );
	}

	/**
	 * @deprecated - 3.6.0, not used, $this->signup_mode is never changed.
	 *
	 * @param bool $enabled - disallow logins?
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function set_signup_mode( $enabled ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->signup_mode = $enabled;
	}

	/**
	 * @deprecated - 3.6.0, not used, value and default are passed to wp_localize_script() in templates/login-form.php.
	 *
	 * @return mixed|string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function modal_button_name() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$name = 'Login';
		if ( isset( $this->extended_settings['modal_trigger_name'] ) && $this->extended_settings['modal_trigger_name'] != '' ) {
			$name = $this->extended_settings['modal_trigger_name'];
		}
		return $name;
	}

	/**
	 * @deprecated - 3.6.0, not used, use WP_Auth0_Options::Instance()->get('custom_signup_fields') instead.
	 *
	 * @return mixed|string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function get_custom_signup_fields() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$fields = $this->wp_options->get( 'custom_signup_fields' );

		if ( trim( $fields ) === '' ) {
			return '[]';
		}

		return $fields;
	}

	/**
	 * @deprecated - 3.6.0, not used, use WP_Auth0_Options::Instance()->get('custom_signup_fields') instead.
	 *
	 * @return mixed|string
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	public function has_custom_signup_fields() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return $this->wp_options->get( 'custom_signup_fields' );
	}
}
