<?php
/**
 * Contains WP_Auth0_Admin_Appearance.
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_Admin_Appearance.
 * Fields and validations for the Embedded settings tab.
 */
class WP_Auth0_Admin_Appearance extends WP_Auth0_Admin_Generic {

	/**
	 * All settings in the Appearance tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = [
			[
				'name'     => __( 'Passwordless Login', 'wp-auth0' ),
				'opt'      => 'passwordless_enabled',
				'id'       => 'wpa0_passwordless_enabled',
				'function' => 'render_passwordless_enabled',
			],
			[
				'name'     => __( 'Icon URL', 'wp-auth0' ),
				'opt'      => 'icon_url',
				'id'       => 'wpa0_icon_url',
				'function' => 'render_icon_url',
			],
			[
				'name'     => __( 'Form Title', 'wp-auth0' ),
				'opt'      => 'form_title',
				'id'       => 'wpa0_form_title',
				'function' => 'render_form_title',
			],
			[
				'name'     => __( 'Enable Gravatar Integration', 'wp-auth0' ),
				'opt'      => 'gravatar',
				'id'       => 'wpa0_gravatar',
				'function' => 'render_gravatar',
			],
			[
				'name'     => __( 'Login Name Style', 'wp-auth0' ),
				'opt'      => 'username_style',
				'id'       => 'wpa0_username_style',
				'function' => 'render_username_style',
			],
			[
				'name'     => __( 'Primary Color', 'wp-auth0' ),
				'opt'      => 'primary_color',
				'id'       => 'wpa0_primary_color',
				'function' => 'render_primary_color',
			],
			[
				'name'     => __( 'Extra Settings', 'wp-auth0' ),
				'opt'      => 'extra_conf',
				'id'       => 'wpa0_extra_conf',
				'function' => 'render_extra_conf',
			],
			[
				'name'     => __( 'Use Custom Lock JS URL', 'wp-auth0' ),
				'opt'      => 'custom_cdn_url',
				'id'       => 'wpa0_custom_cdn_url',
				'function' => 'render_custom_cdn_url',
			],
			[
				'name'     => __( 'Custom Lock JS URL', 'wp-auth0' ),
				'opt'      => 'cdn_url',
				'id'       => 'wpa0_cdn_url',
				'function' => 'render_cdn_url',
			],
			[
				'name'     => __( 'Connections to Show', 'wp-auth0' ),
				'opt'      => 'lock_connections',
				'id'       => 'wpa0_connections',
				'function' => 'render_connections',
			],
		];
		$this->init_option_section( '', 'appearance', $options );
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
	public function render_custom_cdn_url( $args = [] ) {
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
	public function render_cdn_url( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text' );
		$this->render_field_description(
			__( 'This should point to the latest Lock JS available in the CDN and rarely needs to change', 'wp-auth0' )
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
	public function render_passwordless_enabled( $args = [] ) {
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
	 * Render form field and description for the `lock_connections` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_connections( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', 'eg: "sms, google-oauth2, github"' );
		$this->render_field_description(
			__( 'Specify which Social, Database, or Passwordless connections to display in the Auth0 form. ', 'wp-auth0' ) .
			__( 'If this is empty, all enabled connections for this Application will be shown. ', 'wp-auth0' ) .
			__( 'Separate multiple connection names with a comma. ', 'wp-auth0' ) .
			sprintf(
				// translators: HTML link to the Auth0 dashboard.
				__( 'Connections listed here must already be active in your %s', 'wp-auth0' ),
				$this->get_dashboard_link( 'connections/social' )
			) .
			__( ' and enabled for this Application. ', 'wp-auth0' ) .
			__( 'Click on a Connection and use the "Name" value in this field', 'wp-auth0' )
		);
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
	public function render_icon_url( $args = [] ) {
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
	public function render_form_title( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description( __( 'Title used on the Auth0 login form', 'wp-auth0' ) );
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
	public function render_gravatar( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Automatically display an avatar (from Gravatar) on the Auth0 login form', 'wp-auth0' )
		);
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
	public function render_username_style( $args = [] ) {
		$this->render_radio_buttons(
			[
				[
					'label' => 'Auto',
					'value' => '',
				],
				'email',
				'username',
			],
			$args['label_for'],
			$args['opt_name'],
			$this->options->get( $args['opt_name'], '' )
		);
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
	public function render_primary_color( $args = [] ) {
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
	 * Render form field and description for the `extra_conf` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_extra_conf( $args = [] ) {
		$this->render_textarea_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Valid JSON for Lock options configuration; will override all options set elsewhere. ', 'wp-auth0' ) .
			$this->get_docs_link( 'libraries/lock/customization', 'See options and examples' )
		);
	}

	/**
	 * Validation for Basic settings tab.
	 *
	 * @param array $input - New options being saved.
	 *
	 * @return array
	 */
	public function basic_validation( array $input ) {
		$input['passwordless_enabled'] = $this->sanitize_switch_val( $input['passwordless_enabled'] ?? null );

		$input['icon_url'] = esc_url_raw( $this->sanitize_text_val( $input['icon_url'] ?? null ) );
		if ( ! filter_var( $input['icon_url'], FILTER_VALIDATE_URL ) ) {
			$input['icon_url'] = $this->options->get( 'icon_url' );
			self::add_validation_error( __( 'The Icon URL used is not valid.', 'wp-auth0' ) );
		}

		$input['form_title']     = $this->sanitize_text_val( $input['form_title'] ?? null );
		$input['gravatar']       = $this->sanitize_switch_val( $input['gravatar'] ?? null );
		$input['username_style'] = $this->sanitize_text_val( $input['username_style'] ?? null );
		$input['primary_color']  = $this->sanitize_text_val( $input['primary_color'] ?? null );

		$input['extra_conf'] = $this->sanitize_text_val( $input['extra_conf'] ?? null );
		if ( ! empty( $input['extra_conf'] ) && ! json_decode( $input['extra_conf'] ) ) {
			$input['extra_conf'] = $this->options->get( 'extra_conf', '' );
			$error               = __( 'The Extra Settings parameter should be a valid JSON object.', 'wp-auth0' );
			self::add_validation_error( $error );
		}

		$input['custom_cdn_url'] = $this->sanitize_switch_val( $input['custom_cdn_url'] ?? null );

		$input['cdn_url'] = esc_url_raw( $this->sanitize_text_val( $input['cdn_url'] ?? null ) );
		if ( $input['custom_cdn_url'] && ! filter_var( $input['cdn_url'], FILTER_VALIDATE_URL ) ) {
			$input['cdn_url'] = $this->options->get( 'cdn_url', WPA0_LOCK_CDN_URL );
			self::add_validation_error( __( 'The Custom Lock JS URL used is not valid.', 'wp-auth0' ) );
		}

		$input['lock_connections'] = $this->sanitize_text_val( $input['lock_connections'] ?? null );

		return $input;
	}
}
