<?php

class WP_Auth0_Lock {

	const LOCK_GLOBAL_JS_VAR_NAME = 'wpAuth0LockGlobal';

	protected $wp_options;
	protected $extended_settings;
	protected $signup_mode = false;

	/**
	 * WP_Auth0_Lock_Options constructor.
	 *
	 * @param array                 $extended_settings Argument in renderAuth0Form(), used by shortcode and widget.
	 * @param null|WP_Auth0_Options $opts WP_Auth0_Options instance.
	 */
	public function __construct( $extended_settings = [], $opts = null ) {
		$this->wp_options        = ! empty( $opts ) ? $opts : WP_Auth0_Options::Instance();
		$this->extended_settings = $extended_settings;
	}

	public function get_state_obj( $redirect_to = null ) {

		$stateObj = [
			'interim' => ( isset( $_GET['interim-login'] ) && $_GET['interim-login'] == 1 ),
			'nonce'   => WP_Auth0_State_Handler::get_instance()->get_unique(),
		];

		if ( ! empty( $redirect_to ) ) {
			$stateObj['redirect_to'] = addslashes( $redirect_to );
		} elseif ( isset( $_GET['redirect_to'] ) ) {
			$stateObj['redirect_to'] = addslashes( $_GET['redirect_to'] );
		}

		return base64_encode( json_encode( $stateObj ) );
	}

	protected function _is_valid( $array, $key ) {
		return isset( $array[ $key ] ) && trim( $array[ $key ] ) !== '';
	}

	protected function build_settings( $settings ) {
		$options_obj = [];

		// Widget or shortcode languageDictionary.
		if ( ! empty( $settings['dict'] ) ) {
			$options_obj['languageDictionary'] = $settings['dict'];
		}

		if ( isset( $settings['form_title'] ) && trim( $settings['form_title'] ) !== '' ) {

			if ( ! isset( $options_obj['languageDictionary'] ) ) {
				$options_obj['languageDictionary'] = [];
			}

			$options_obj['languageDictionary']['title'] = $settings['form_title'];
		}

		$options_obj['socialButtonStyle'] = 'big';

		if ( isset( $settings['gravatar'] ) && '' !== $settings['gravatar'] && empty( $settings['gravatar'] ) ) {
			$options_obj['avatar'] = null;
		}

		if ( ! empty( $settings['gravatar'] ) ) {
			$options_obj['avatar'] = true;
		}

		if ( $this->_is_valid( $settings, 'username_style' ) ) {
			$options_obj['usernameStyle'] = $settings['username_style'];
		}

		if ( $this->_is_valid( $settings, 'icon_url' ) || $this->_is_valid( $settings, 'primary_color' ) ) {
			$options_obj['theme'] = [];
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

		return $options_obj;
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

		$extraOptions = [
			'auth' => [
				'params' => [
					'state' => $this->get_state_obj( $redirect_to ),
					'scope' => WP_Auth0_LoginManager::get_userinfo_scope( 'lock' ),
				],
			],
		];

		$extraOptions['auth']['params']['nonce'] = WP_Auth0_Nonce_Handler::get_instance()->get_unique();
		$extraOptions['auth']['responseType']    = 'code';
		$extraOptions['auth']['redirectUrl']     = $this->wp_options->get_wp_auth0_url( $this->get_callback_protocol() );

		if ( $this->wp_options->get( 'custom_domain' ) ) {
			$tenant_region                        = wp_auth0_get_tenant_region( $this->wp_options->get( 'domain' ) );
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

		$show_as_modal = isset( $this->extended_settings['show_as_modal'] ) && $this->extended_settings['show_as_modal'];
		if ( ! $show_as_modal ) {
			$options_obj['container'] = WPA0_AUTH0_LOGIN_FORM_ID;
		}

		if ( ! $this->wp_options->is_wp_registration_enabled() ) {
			$options_obj['disableSignupAction'] = true;
		}

		if ( wp_auth0_is_current_login_action( [ 'register' ] ) ) {
			$options_obj['initialScreen'] = 'signUp';
		}

		return apply_filters( 'auth0_lock_options', $options_obj );
	}

	/**
	 * Get the protocol to use for callback URLs.
	 *
	 * @return null|string - Returns 'https' if forced, null (use site default) if not.
	 */
	private function get_callback_protocol() {
		return $this->wp_options->get( 'force_https_callback' ) ? 'https' : null;
	}

	/**
	 * Render a link at the bottom of a WordPress core login form back to Lock.
	 */
	public static function render_back_to_lock() {
		$title = wp_auth0_get_option( 'form_title' );
		if ( empty( $title ) ) {
			$title = 'Auth0';
		}

		printf(
			'<div id="extra-options"><a href="?">%s</a></div>',
			// translators: The $title variable is the admin-controlled form title.
			sanitize_text_field( sprintf( __( '← Back to %s login', 'wp-auth0' ), $title ) )
		);
	}

	/**
	 * Render the Lock form with saved and passed options.
	 *
	 * @param bool  $canShowLegacyLogin - Is the legacy login form allowed? Only on wp-login.php.
	 * @param array $specialSettings - Additional settings from widget or shortcode.
	 */
	public static function render( $canShowLegacyLogin = true, $specialSettings = [] ) {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( $canShowLegacyLogin && wp_auth0_can_show_wp_login_form() ) {
			add_action( 'login_footer', [ 'WP_Auth0_Lock', 'render_back_to_lock' ] );
			add_action( 'woocommerce_after_customer_login_form', [ 'WP_Auth0_Lock', 'render_back_to_lock' ] );
			return;
		}

		$options      = WP_Auth0_Options::Instance();
		$lock_options = new self( $specialSettings );

		wp_enqueue_script( 'wpa0_lock', $options->get_lock_url(), [ 'jquery' ], false, true );
		wp_enqueue_script( 'js-cookie', WPA0_PLUGIN_LIB_URL . 'js.cookie.min.js', false, '2.2.0', true );
		wp_enqueue_script( 'wpa0_lock_init', WPA0_PLUGIN_JS_URL . 'lock-init.js', [ 'jquery' ], WPA0_VERSION, true );

		wp_localize_script(
			'wpa0_lock_init',
			WP_Auth0_Lock::LOCK_GLOBAL_JS_VAR_NAME,
			[
				'settings'        => $lock_options->get_lock_options(),
				'ready'           => wp_auth0_is_ready(),
				'domain'          => $options->get_auth_domain(),
				'clientId'        => $options->get( 'client_id' ),
				'stateCookieName' => WP_Auth0_State_Handler::get_storage_cookie_name(),
				'nonceCookieName' => WP_Auth0_Nonce_Handler::get_storage_cookie_name(),
				'usePasswordless' => $options->get( 'passwordless_enabled' ),
				'loginFormId'     => WPA0_AUTH0_LOGIN_FORM_ID,
				'showAsModal'     => ! empty( $specialSettings['show_as_modal'] ),
				'i18n'            => [
					'notReadyText'       => __( 'Auth0 is not configured', 'wp-auth0' ),
					'cannotFindNodeText' => __( 'Auth0 cannot find node with id ', 'wp-auth0' ),
					'modalButtonText'    => ! empty( $specialSettings['modal_trigger_name'] )
						? sanitize_text_field( $specialSettings['modal_trigger_name'] )
						: __( 'Login', 'wp-auth0' ),
				],
			]
		);

		$login_tpl = WPA0_PLUGIN_DIR . 'templates/auth0-login-form.php';
		$login_tpl = apply_filters( 'auth0_login_form_tpl', $login_tpl, $lock_options, $canShowLegacyLogin );
		require $login_tpl;
	}
}
