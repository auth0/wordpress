<?php

class WP_Auth0_WooCommerceOverrides {
	protected $plugin;
	protected $options;

	/**
	 * WP_Auth0_WooCommerceOverrides constructor.
	 *
	 * @param WP_Auth0              $plugin
	 * @param null|WP_Auth0_Options $options
	 */
	public function __construct( WP_Auth0 $plugin, $options = null ) {
		$this->plugin = $plugin;
		if ( $options == null ) {
			$this->options = \WP_Auth0_Options::Instance();
		} else {
			$this->options = $options;
		}
	}

	/**
	 * @deprecated - 3.10.0, will move add_action calls out of this class in the next major.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function init() {
		add_filter( 'woocommerce_checkout_login_message', [ $this, 'override_woocommerce_checkout_login_form' ] );
		add_filter( 'woocommerce_before_customer_login_form', [ $this, 'override_woocommerce_login_form' ] );
	}

	private function render_login_form( $redirectPage ) {
		$this->plugin->render_auth0_login_css();
		if ( $this->options->get( 'auto_login', false ) ) {
			// Redirecting to WordPress login area
			$redirectUrl = get_permalink( wc_get_page_id( $redirectPage ) );
			$loginUrl    = wp_login_url( $redirectUrl );

			printf( "<a class='button' href='%s'>%s</a>", $loginUrl, __( 'Login', 'wp-auth0' ) );
		} else {
			echo $this->plugin->render_form( '' );
		}
	}

	public function override_woocommerce_checkout_login_form( $html ) {
		$this->render_login_form( 'checkout' );

		if ( wp_auth0_can_show_wp_login_form() ) {
			echo '<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>';
		}
	}

	public function override_woocommerce_login_form( $html ) {
		$this->render_login_form( 'myaccount' );
	}
}
