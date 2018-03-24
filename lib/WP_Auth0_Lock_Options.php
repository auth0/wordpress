<?php
// TODO: Deprecate - previously used for passwordless
class WP_Auth0_Lock_Options {

	protected $wp_options;
	protected $extended_settings;

	protected $signup_mode = false;

	public function __construct( $extended_settings = array() ) {
		$this->wp_options = WP_Auth0_Options::Instance();
		$this->extended_settings = $extended_settings;
	}

	public function get_lock_classname() {
		if ( $this->_get_boolean( $this->wp_options->get( 'passwordless_enabled' ) ) ) {
			return 'Auth0LockPasswordless';
		} else {
			return 'Auth0Lock';
		}
	}

	public function isPasswordlessEnable() {
		return $this->_get_boolean( $this->wp_options->get( 'passwordless_enabled' ) );
	}

	public function get_lock_show_method() {
		return 'show';
	}

	public function get_code_callback_url() {
		$protocol = $this->_get_boolean( $this->wp_options->get( 'force_https_callback' ) ) ? 'https' : null;
		return $this->wp_options->get_wp_auth0_url( $protocol );
  }

	public function get_implicit_callback_url() {
		return wp_login_url();
	}

	public function get_sso() {
		return $this->_get_boolean( $this->wp_options->get( 'sso' ) );
	}

	public function get_custom_css() {
		return $this->wp_options->get( 'custom_css' );
	}

	public function get_custom_js() {
		return $this->wp_options->get( 'custom_js' );
	}

	public function can_show() {
		return trim( $this->get_client_id() ) !== '' && trim( $this->get_domain() ) !== '';
	}

	public function get_client_id() {
		return $this->wp_options->get( 'client_id' );
	}

	public function get_domain() {
		return $this->wp_options->get( 'domain' );
	}

	public function get_cdn_url() {
		return $this->wp_options->get( 'cdn_url' );
	}

	public function get_wordpress_login_enabled() {
		return $this->_get_boolean( $this->wp_options->get( 'wordpress_login_enabled' ) );
	}

	public function get_auth0_implicit_workflow() {
		return $this->_get_boolean( $this->wp_options->get( 'auth0_implicit_workflow' ) );
	}

	public function set_signup_mode( $enabled ) {
		$this->signup_mode = $enabled;
	}

	public function is_registration_enabled() {
		return $this->wp_options->is_wp_registration_enabled();
	}

	public function show_as_modal() {
		return isset( $this->extended_settings['show_as_modal'] ) && $this->extended_settings['show_as_modal'];
	}

	public function modal_button_name() {
		$name = 'Login';
		if ( isset( $this->extended_settings['modal_trigger_name'] ) && $this->extended_settings['modal_trigger_name'] != '' ) {
			$name = $this->extended_settings['modal_trigger_name'];
		}
		return $name;
	}

	public function get_state_obj( $redirect_to = null ) {
		$stateObj = array(
			'interim' => ( isset( $_GET['interim-login'] ) && $_GET['interim-login'] == 1 ),
			'nonce' => WP_Auth0_Nonce_Handler::getInstance()->get()
		);
		if ( !empty( $redirect_to ) ) {
			$stateObj["redirect_to"] = addslashes( $redirect_to );
		}
		elseif ( isset( $_GET['redirect_to'] ) ) {
			$stateObj["redirect_to"] = addslashes( $_GET['redirect_to'] );
		}

		return base64_encode( json_encode( $stateObj ) );
	}

	protected function _get_boolean( $value ) {
		return 1 === (int) $value || strtolower( $value ) === 'true';
	}

	protected function _is_valid( $array, $key ) {
		return isset( $array[$key] ) && trim( $array[$key] ) !== '';
	}

	protected function build_settings( $settings ) {
		$options_obj = array();
		if ( isset( $settings['form_title'] ) && trim( $settings['form_title'] ) !== '' ) {

			$options_obj['dict'] = array(
				'signin' => array(
					'title' => $settings['form_title'],
				)
			);

		} 

		if ( isset( $settings['language_dictionary'] ) && trim( $settings['language_dictionary'] ) !== '' ) {
			if ( $oDict = json_decode( $settings['language_dictionary'], true ) ) {
				$options_obj['dict'] = $oDict;
			} 
		}

		if ( isset( $settings['language'] ) && trim( $settings['language'] ) !== '' ) {
			$options_obj['dict'] = $settings['language'];
		}

		
		if ( $this->_is_valid( $settings, 'social_big_buttons' ) ) {
			$options_obj['socialBigButtons'] = $this->_get_boolean( $settings['social_big_buttons'] );
		}
		if ( $this->_is_valid( $settings, 'gravatar' ) ) {
			$options_obj['gravatar'] = $this->_get_boolean( $settings['gravatar'] );
		}
		if ( $this->_is_valid( $settings, 'username_style' ) ) {
			$options_obj['usernameStyle'] = $settings['username_style'];
		}
		if ( $this->_is_valid( $settings, 'sso' ) ) {
			$options_obj['sso'] = $this->_get_boolean( $settings['sso'] );
		}
		if ( $this->_is_valid( $settings, 'icon_url' ) ) {
			$options_obj['icon'] = $settings['icon_url'];
		}
		if ( $this->_is_valid( $settings, 'lock_connections' ) ) {
			$options_obj['connections'] = $this->wp_options->get_lock_connections();
		}
		if ( isset( $settings['extra_conf'] ) && trim( $settings['extra_conf'] ) !== '' ) {
			$extra_conf_arr = json_decode( $settings['extra_conf'], true );
			$options_obj = array_merge( $extra_conf_arr, $options_obj );
		}
		if ( $this->signup_mode ) {
			$options_obj["mode"] = "signup";
		}
		return $options_obj;
	}

	public function get_sso_options() {
		$options = $this->get_lock_options();

		$options["scope"] = "openid email identities ";

		if ( $this->get_auth0_implicit_workflow() ) {
			$options["callbackOnLocationHash"] = true;
			$options["callbackURL"] = $this->get_implicit_callback_url();
			$options["scope"] .= "name email nickname email_verified identities";
		} else {
			$options["callbackOnLocationHash"] = false;
			$options["callbackURL"] = $this->get_code_callback_url();
		}

		$redirect_to = null;

		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect_to = $_GET['redirect_to'];
		} else {
			$redirect_to = home_url( $_SERVER["REQUEST_URI"] );
		}

		unset( $options["authParams"] );
		$options["state"] = $this->get_state_obj( $redirect_to );

		return $options;

	}

	public function get_lock_options() {
		$extended_settings = $this->extended_settings;
		if ( isset( $extended_settings['show_as_modal'] ) ) unset( $extended_settings['show_as_modal'] );
		if ( isset( $extended_settings['modal_trigger_name'] ) ) unset( $extended_settings['modal_trigger_name'] );

		$redirect_to = null;
		if ( isset( $this->extended_settings['redirect_to'] ) ) {
			$redirect_to = $this->extended_settings['redirect_to'];
		}
		$state = $this->get_state_obj( $redirect_to );

		$options_obj = $this->build_settings( $this->wp_options->get_options() );
		$extended_settings = $this->build_settings( $extended_settings );

		$extraOptions = array(
			"authParams"    => array( "state" => $state ),
		);

		$extraOptions["authParams"]["scope"] = "openid ";

		if ( $this->get_auth0_implicit_workflow() ) {
			$extraOptions["authParams"]["scope"] .= "name email nickname email_verified identities";
		} else {
			$extraOptions["responseType"] = 'code';
			$extraOptions["callbackURL"] = $this->get_code_callback_url();
		}

		$options_obj = array_merge( $extraOptions, $options_obj, $extended_settings );

		if ( ! $this->show_as_modal() ) {
			$options_obj['container'] = 'auth0-login-form';
		}

		if ( ! $this->is_registration_enabled() ) {
			$options_obj['disableSignupAction'] = true;
		}

		return $options_obj;
	}
}
