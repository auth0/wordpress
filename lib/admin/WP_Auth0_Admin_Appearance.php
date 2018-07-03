<?php

class WP_Auth0_Admin_Appearance extends WP_Auth0_Admin_Generic {

	/**
	 *
	 * @deprecated 3.6.0 - Use $this->_description instead
	 */
	const APPEARANCE_DESCRIPTION = '';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
	);

	/**
	 * WP_Auth0_Admin_Appearance constructor.
	 *
	 * @param WP_Auth0_Options_Generic $options
	 */
	public function __construct( WP_Auth0_Options_Generic $options ) {
		parent::__construct( $options );
		$this->_description = __( 'Settings related to the way the login widget is shown.', 'wp-auth0' );
	}

	/**
	 * All settings in the Appearance tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = array(
			array(
				'name'     => __( 'Icon URL', 'wp-auth0' ),
				'opt'      => 'icon_url',
				'id'       => 'wpa0_icon_url',
				'function' => 'render_icon_url',
			),
			array(
				'name'     => __( 'Form Title', 'wp-auth0' ),
				'opt'      => 'form_title',
				'id'       => 'wpa0_form_title',
				'function' => 'render_form_title',
			),
			array(
				'name'     => __( 'Large Social Buttons', 'wp-auth0' ),
				'opt'      => 'social_big_buttons',
				'id'       => 'wpa0_social_big_buttons',
				'function' => 'render_social_big_buttons',
			),
			array(
				'name'     => __( 'Enable Gravatar Integration', 'wp-auth0' ),
				'opt'      => 'gravatar',
				'id'       => 'wpa0_gravatar',
				'function' => 'render_gravatar',
			),
			array(
				'name'     => __( 'Login Form CSS', 'wp-auth0' ),
				'opt'      => 'custom_css',
				'id'       => 'wpa0_custom_css',
				'function' => 'render_custom_css',
			),
			array(
				'name'     => __( 'Login Form JS', 'wp-auth0' ),
				'opt'      => 'custom_js',
				'id'       => 'wpa0_custom_js',
				'function' => 'render_custom_js',
			),
			array(
				'name'     => __( 'Login Name Style', 'wp-auth0' ),
				'opt'      => 'username_style',
				'id'       => 'wpa0_username_style',
				'function' => 'render_username_style',
			),
			array(
				'name'     => __( 'Primary Color', 'wp-auth0' ),
				'opt'      => 'primary_color',
				'id'       => 'wpa0_primary_color',
				'function' => 'render_primary_color',
			),
			array(
				'name'     => __( 'Language', 'wp-auth0' ),
				'opt'      => 'language',
				'id'       => 'wpa0_language',
				'function' => 'render_language',
			),
			array(
				'name'     => __( 'Language Dictionary', 'wp-auth0' ),
				'opt'      => 'language_dictionary',
				'id'       => 'wpa0_language_dictionary',
				'function' => 'render_language_dictionary',
			),
		);
		$this->init_option_section( '', 'appearance', $options );
	}

	/**
	 * Render form field and description for the `icon_url` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_icon_url( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		printf( ' <a id="wpa0_choose_icon" class="button-secondary">%s</a>', __( 'Choose Icon', 'wp-auth0' ) );
		$this->render_field_description(
			__( 'Icon above the title on the Auth0 login form. ', 'wp-auth0' ) .
			__( 'This image works best as a PNG with a transparent background less than 120px tall', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `form_title` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_form_title( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description( __( 'Title used on the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render form field and description for the `social_big_buttons` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_social_big_buttons( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description( __( 'Use large social login buttons on the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render form field and description for the `gravatar` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_gravatar( $args = array() ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Automatically display an avatar (from Gravatar) on the Auth0 login form', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `custom_css` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_custom_css( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description( __( 'Valid CSS to customize the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render form field and description for the `custom_js` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_custom_js( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description( __( 'Valid JS to customize the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render form field and description for the `username_style` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_username_style( $args = array() ) {
		$opt_name = $args['opt_name'];
		$id_attr  = $args['label_for'];
		$value    = $this->options->get( $opt_name );
		$this->render_radio_button( $id_attr . '_au', $opt_name, '', 'Auto', empty( $value ) );
		$this->render_radio_button( $id_attr . '_em', $opt_name, 'email', '', 'email' === $value );
		$this->render_radio_button( $id_attr . '_un', $opt_name, 'username', '', 'username' === $value );
		$this->render_field_description(
			__( 'To allow the user to use either email or username to login, leave this as "Auto." ', 'wp-auth0' ) .
			__( 'Only database connections that require a username will allow username logins', 'wp-auth0' )
		);
	}

	/**
	 * Render form field and description for the `primary_color` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_primary_color( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Primary color for the Auth0 login form in hex format. ', 'wp-auth0' ) .
			$this->get_docs_link(
				'libraries/lock/v11/configuration#primarycolor-string-',
				__( 'More information on this settings', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `language` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_language( $args = array() ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'The language parameter for the Auth0 login form. ', 'wp-auth0' ) .
			sprintf(
				'<a href="https://github.com/auth0/lock/tree/master/src/i18n" target="_blank">%s</a>',
				__( 'Available languages list', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render form field and description for the `language_dictionary` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_language_dictionary( $args = array() ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'The languageDictionary parameter for the Auth0 login form. ', 'wp-auth0' ) .
			sprintf(
				'<a href="https://github.com/auth0/lock/blob/master/src/i18n/en.js" target="_blank">%s</a>',
				__( 'List of all modifiable options', 'wp-auth0' )
			)
		);
	}

	public function basic_validation( $old_options, $input ) {
		$input['form_title']         = sanitize_text_field( $input['form_title'] );
		$input['icon_url']           = esc_url( $input['icon_url'], array( 'http', 'https' ) );
		$input['social_big_buttons'] = ( isset( $input['social_big_buttons'] ) ? $input['social_big_buttons'] : 0 );
		$input['gravatar']           = ( isset( $input['gravatar'] ) ? $input['gravatar'] : 0 );
		$input['language']           = sanitize_text_field( $input['language'] );
		$input['primary_color']      = sanitize_text_field( $input['primary_color'] );

		if ( trim( $input['language_dictionary'] ) !== '' ) {
			if ( json_decode( $input['language_dictionary'] ) === null ) {
				$error = __( 'The language dictionary parameter should be a valid json object.', 'wp-auth0' );
				$this->add_validation_error( $error );
				$input['language'] = $old_options['language'];
			}
		}
		return $input;
	}

	/**
	 *
	 * @deprecated 3.6.0 - Handled by WP_Auth0_Admin_Generic::render_description()
	 */
	public function render_appearance_description() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		printf( '<p class="a0-step-text">%s</p>', $this->_description );
	}
}
