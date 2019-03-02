<?php

class WP_Auth0_WooCommerceOverrides {
	protected $plugin;

	public function __construct( WP_Auth0 $plugin, $options ) {
		$this->plugin = $plugin;
		$this->options = $options;
	}

	public function init() {
		add_filter( 'woocommerce_checkout_login_message', array( $this, 'override_woocommerce_checkout_login_form' ) );
		add_filter( 'woocommerce_before_customer_login_form', array( $this, 'override_woocommerce_login_form' ) );
	}

	private function render_login_form( $redirect ) {
		$this->plugin->render_auth0_login_css();
		if ($this->options->get('auto_login',false)) {
			$redirection_after = site_url( $redirect );
			// Redirecting to Wordpress login area
			$loginUrl = wp_login_url( $redirection_after );
	
			echo '<a class="button button-primary" href="'.$loginUrl.'">Login</a>';
		} else {
			echo $this->plugin->shortcode( array() );
		}
	}

	public function override_woocommerce_checkout_login_form( $html ) {
		$this->render_login_form('/checkout/');

		if ( isset( $_GET['wle'] ) ) {
			echo '<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>';
		}
	}

	public function override_woocommerce_login_form( $html ) {
		$this->render_login_form('/my-account/');
	} 
}
