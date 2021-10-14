<?php
/**
 * Contains WP_Auth0_Admin_Features.
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_Admin_Features.
 * Fields and validations for the Features settings tab.
 */
class WP_Auth0_Admin_Features extends WP_Auth0_Admin_Generic {

	/**
	 * All settings in the Features tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = [
			[
				'name'     => __( 'Universal Login Page', 'wp-auth0' ),
				'opt'      => 'auto_login',
				'id'       => 'wpa0_auto_login',
				'function' => 'render_auto_login',
			],
			[
				'name'     => __( 'Auto Login Parameters', 'wp-auth0' ),
				'opt'      => 'auto_login_params',
				'id'       => 'wpa0_auto_login_params',
				'function' => 'render_auto_login_params',
			],
			[
				'name'     => __( 'Auto Login Method', 'wp-auth0' ),
				'opt'      => 'auto_login_method',
				'id'       => 'wpa0_auto_login_method',
				'function' => 'render_auto_login_method',
			],
			[
				'name'     => __( 'Auth0 Logout', 'wp-auth0' ),
				'opt'      => 'singlelogout',
				'id'       => 'wpa0_singlelogout',
				'function' => 'render_singlelogout',
			],
			[
				'name'     => __( 'Override WordPress Avatars', 'wp-auth0' ),
				'opt'      => 'override_wp_avatars',
				'id'       => 'wpa0_override_wp_avatars',
				'function' => 'render_override_wp_avatars',
			],
		];

		$this->init_option_section( '', 'features', $options );
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
	public function render_singlelogout( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Turning this on will log users out of Auth0 when they log out of WordPress.', 'wp-auth0' )
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
	public function render_auto_login( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_auto_login_options' );
		$this->render_field_description(
			__( 'Use the Universal Login Page (ULP) for authentication and SSO. ', 'wp-auth0' ) .
			__( 'When turned on, <code>wp-login.php</code> will redirect to the hosted login page. ', 'wp-auth0' ) .
			__( 'When turned off, <code>wp-login.php</code> will show an embedded login form. ', 'wp-auth0' ) .
			$this->get_docs_link( 'guides/login/universal-vs-embedded', __( 'More on ULP vs embedded here', 'wp-auth0' ) )
		);
	}

	/**
	 * Render form field and description for the `auto_login_params` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Auth0_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_login_params( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], '', '', '', 'wpa0_auto_login_options' );
		$this->render_field_description(
			__( 'Optional. Here you can specify additional parameters to pass to the the Universal Login Page (ULP) during authentication. ', 'wp-auth0' ) .
			__( 'For example, you can specify <code>screen_hint=signup</code> or <code>prompt=login</code> parameters here. ', 'wp-auth0' ) .
			$this->get_docs_link( 'docs/login/universal-login/new-experience', __( 'Learn more about available ULP parameters here', 'wp-auth0' ) )
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
	public function render_auto_login_method( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], '', '', '', 'wpa0_auto_login_options' );
		$this->render_field_description(
			__( 'Enter a name here to automatically use a single, specific connection to login . ', 'wp-auth0' ) .
			sprintf(
				// translators: Placeholder is an HTML link to the Auth0 dashboard.
				__( 'Find the method name to use under Connections > [Connection Type] in your %s. ', 'wp-auth0' ),
				$this->get_dashboard_link()
			) .
			__( 'Click the expand icon and use the value in the "Name" field (like "google-oauth2")', 'wp-auth0' )
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
	public function render_override_wp_avatars( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Overrides the WordPress avatar with the Auth0 profile avatar', 'wp-auth0' )
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
		$input['auto_login']          = $this->sanitize_switch_val( $input['auto_login'] ?? null );
		$input['auto_login_params']   = $this->sanitize_query_parameters( $input['auto_login_params'] ?? null );
		$input['auto_login_method']   = $this->sanitize_text_val( $input['auto_login_method'] ?? null );
		$input['singlelogout']        = $this->sanitize_switch_val( $input['singlelogout'] ?? null );
		$input['override_wp_avatars'] = $this->sanitize_switch_val( $input['override_wp_avatars'] ?? null );
		return $input;
	}
}
