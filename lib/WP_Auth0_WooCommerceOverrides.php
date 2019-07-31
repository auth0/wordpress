<?php
/**
 * Contains Class WP_Auth0_WooCommerceOverrides class.
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_WooCommerceOverrides.
 */
class WP_Auth0_WooCommerceOverrides {

	/**
	 * Injected WP_Auth0 instance.
	 *
	 * @var WP_Auth0
	 */
	protected $plugin;

	/**
	 * Injected WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $options;

	/**
	 * WP_Auth0_WooCommerceOverrides constructor.
	 *
	 * @param WP_Auth0         $plugin - WP_Auth0 instance.
	 * @param WP_Auth0_Options $options - WP_Auth0_Options instance.
	 */
	public function __construct( WP_Auth0 $plugin, WP_Auth0_Options $options ) {
		$this->plugin  = $plugin;
		$this->options = $options;
	}

	/**
	 * Render the login form or link to ULP.
	 *
	 * @param string $redirect_page - Page slug to redirect to after logging in.
	 */
	private function render_login_form( $redirect_page ) {
		$this->plugin->render_auth0_login_css();
		if ( $this->options->get( 'auto_login', false ) ) {
			// Redirecting to WordPress login page.
			$redirect_url = get_permalink( wc_get_page_id( $redirect_page ) );
			$login_url    = wp_login_url( $redirect_url );

			printf( "<a class='button' href='%s'>%s</a>", $login_url, __( 'Login', 'wp-auth0' ) );
		} else {
			echo $this->plugin->render_form( '' );
		}
	}

	/**
	 * Handle Auth0 login on the checkout form if the plugin is ready.
	 *
	 * @param string $html - Original HTML passed to filter.
	 *
	 * @return mixed
	 */
	public function override_woocommerce_checkout_login_form( $html ) {

		if ( ! WP_Auth0::ready() ) {
			return $html;
		}

		$this->render_login_form( 'checkout' );

		if ( wp_auth0_can_show_wp_login_form() ) {
			echo '<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>';
		}
	}

	/**
	 * Handle Auth0 login on the account form if the plugin is ready.
	 *
	 * @param $html string $html - Original HTML passed to filter.
	 *
	 * @return mixed
	 */
	public function override_woocommerce_login_form( $html ) {

		if ( ! WP_Auth0::ready() ) {
			return $html;
		}

		$this->render_login_form( 'myaccount' );
	}
}
