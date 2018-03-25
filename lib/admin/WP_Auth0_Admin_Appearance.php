<?php

class WP_Auth0_Admin_Appearance extends WP_Auth0_Admin_Generic {

	protected $description = 'Settings related to the way the login widget is shown';
	protected $actions_middlewares = array(
		'basic_validation',
	);

	/**
	 * Sets up settings field registration
	 */
	public function init() {
		$this->init_option_section( '', 'appearance', array(
			array( 'id' => 'wpa0_icon_url',
			       'name' => __( 'Icon URL', 'wp-auth0' ),
			       'function' => 'render_icon_url' ),
			array( 'id' => 'wpa0_form_title',
			       'name' => __( 'Form Title', 'wp-auth0' ),
			       'function' => 'render_form_title' ),
			array( 'id' => 'wpa0_social_big_buttons',
			       'name' => __( 'Big Social Buttons', 'wp-auth0' ),
			       'function' => 'render_social_big_buttons' ),
			array( 'id' => 'wpa0_gravatar',
			       'name' => __( 'Enable Gravatar Integration', 'wp-auth0' ),
			       'function' => 'render_gravatar' ),
			array( 'id' => 'wpa0_custom_css',
			       'name' => __( 'Login Form CSS', 'wp-auth0' ),
			       'function' => 'render_custom_css' ),
			array( 'id' => 'wpa0_custom_js',
			       'name' => __( 'Login Form JS', 'wp-auth0' ),
			       'function' => 'render_custom_js' ),
			array( 'id' => 'wpa0_username_style',
			       'name' => __( 'Login Name Style', 'wp-auth0' ),
			       'function' => 'render_username_style' ),
			array( 'id' => 'wpa0_primary_color',
			       'name' => __( 'Primary Color', 'wp-auth0' ),
			       'function' => 'render_primary_color' ),
			array( 'id' => 'wpa0_language',
			       'name' => __( 'Language', 'wp-auth0' ),
			       'function' => 'render_language' ),
			array( 'id' => 'wpa0_language_dictionary',
			       'name' => __( 'Language Dictionary', 'wp-auth0' ),
			       'function' => 'render_language_dictionary' ),
		) );
	}

	/**
	 * Render icon_url field and select button
	 */
	public function render_icon_url() {
		$this->render_text_field( 'wpa0_icon_url', 'icon_url' );
		printf(
			' <a id="wpa0_choose_icon" href="#wpa0_choose_icon" class="button-secondary">%s</a>',
			__( 'Choose Icon', 'wp-auth0' )
		);
		$this->render_field_description( __( 'Icon above the title on the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render form_title field
	 */
	public function render_form_title() {
		$this->render_text_field( 'wpa0_form_title', 'form_title' );
		$this->render_field_description( __( 'Title for the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render social_big_buttons
	 */
	public function render_social_big_buttons() {
		$this->render_switch( 'wpa0_social_big_buttons', 'social_big_buttons' );
		$this->render_field_description( __( 'Uses full-width social login buttons when activated', 'wp-auth0' ) );
	}

	/**
	 * Render gravatar on/off switch
	 */
	public function render_gravatar() {
		$this->render_switch( 'wpa0_gravatar', 'gravatar' );
		$this->render_field_description(
			__( 'Automatically display avatar on login form when email address is entered', 'wp-auth0' )
		);
	}

	/**
	 * Render the custom CSS textarea
	 */
	public function render_custom_css() {
		$this->render_textarea_field( 'wpa0_custom_css', 'custom_css' );
		$this->render_field_description(
			__( 'Valid CSS to customize the Auth0 login form', 'wp-auth0' )
		);
	}

	/**
	 * Render the custom JS textarea
	 */
	public function render_custom_js() {
		$this->render_textarea_field( 'wpa0_custom_js', 'custom_js' );
		$this->render_field_description( __( 'Valid JS to customize the Auth0 login form', 'wp-auth0' ) );
	}

	/**
	 * Render username style radio buttons
	 */
	public function render_username_style() {
		$value = $this->options->get( 'username_style' );
		$this->render_radio_button( 'wpa0_username_style_au', 'username_style', '', 'Auto', empty( $value ) );
		$this->render_radio_button( 'wpa0_username_style_em', 'username_style', 'email', '', 'email' === $value );
		$this->render_radio_button( 'wpa0_username_style_un', 'username_style', 'username', '', 'username' === $value );
		$this->render_field_description(
			__( 'To allow the user to use either email or username to login, leave this as "Auto;" ', 'wp-auth0' ) .
			$this->get_docs_link(
				'libraries/lock/customization#usernamestyle-string-',
				__( 'more information here', 'wp-auth0'	)
			)
		);
	}

	/**
	 * Render primary color field
	 */
	public function render_primary_color() {
		$this->render_text_field( 'wpa0_primary_color', 'primary_color' );
		$this->render_field_description(
			__( 'Primary color for the Auth0 login form in hex format; ', 'wp-auth0' ) .
			$this->get_docs_link(
				'https://auth0.com/docs/libraries/lock/v11/configuration#primarycolor-string-',
				__( 'more information here', 'wp-auth0'	)
			)
		);
	}

	/**
	 * Render language field
	 */
	public function render_language() {
		$this->render_text_field( 'wpa0_language', 'language' );
		$this->render_field_description(
			__( 'The language parameter for the Auth0 login form; ', 'wp-auth0' ) .
			sprintf(
				'<a href="https://github.com/auth0/lock/tree/master/src/i18n" target="_blank">%s</a>',
				__( 'available languages', 'wp-auth0' )
			)
		);
	}

	/**
	 * Render the custom CSS textarea
	 */
	public function render_language_dictionary() {
		$this->render_textarea_field( 'wpa0_language_dictionary', 'language_dictionary' );
		$this->render_field_description(
			__( 'The languageDictionary parameter for the Auth0 login form; ', 'wp-auth0' ) .
			sprintf(
				'<a href="https://github.com/auth0/lock/blob/master/src/i18n/en.js" target="_blank">%s</a>',
				__( 'all modifiable text', 'wp-auth0' )
			)
		);
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
    $input['form_title'] = sanitize_text_field( $input['form_title'] );
		$input['icon_url'] = esc_url( $input['icon_url'], array( 'http', 'https' ) );
		$input['social_big_buttons'] = ( isset( $input['social_big_buttons'] ) ? $input['social_big_buttons'] : 0 );
		$input['gravatar'] = ( isset( $input['gravatar'] ) ? $input['gravatar'] : 0 );
    $input['language'] = sanitize_text_field( $input['language'] );
    $input['primary_color'] = sanitize_text_field( $input['primary_color'] );

    if ( trim( $input['language_dictionary'] ) !== '' ) {
      if ( json_decode( $input['language_dictionary'] ) === null ) {
        $error = __( 'The Appearance > Lock Language Dictionary parameter must be a valid JSON object.', 'wp-auth0' );
        $this->add_validation_error( $error );
        $input['language'] = $old_options['language'];
      }
    }

		return $input;
	}
}