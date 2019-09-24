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
	 * WP_Auth0_Admin_Features constructor.
	 *
	 * @param WP_Auth0_Options $options - Instance of the WP_Auth0_Options class.
	 */
	public function __construct( WP_Auth0_Options $options ) {
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
		$options = [
			[
				'name'     => __( 'Universal Login Page', 'wp-auth0' ),
				'opt'      => 'auto_login',
				'id'       => 'wpa0_auto_login',
				'function' => 'render_auto_login',
			],
			[
				'name'     => __( 'Auto Login Method', 'wp-auth0' ),
				'opt'      => 'auto_login_method',
				'id'       => 'wpa0_auto_login_method',
				'function' => 'render_auto_login_method',
			],
			[
				'name'     => __( 'Single Logout', 'wp-auth0' ),
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
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wpa0_auto_login_method' );
		$this->render_field_description(
			__( 'Use the Universal Login Page (ULP) for authentication and SSO. ', 'wp-auth0' ) .
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
	public function render_auto_login_method( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
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
	 * @param array $old_options - Options before saving the settings form.
	 * @param array $input - New options being saved.
	 *
	 * @return array
	 */
	public function basic_validation( $old_options, $input ) {
		$input['auto_login']          = empty( $input['auto_login'] ) ? 0 : 1;
		$input['auto_login_method']   = isset( $input['auto_login_method'] )
			? sanitize_text_field( $input['auto_login_method'] ) : '';
		$input['singlelogout']        = empty( $input['singlelogout'] ) ? 0 : 1;
		$input['override_wp_avatars'] = empty( $input['override_wp_avatars'] ) ? 0 : 1;

		return $input;
	}
}
