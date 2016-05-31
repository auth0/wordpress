<?php

class WP_Auth0_WooCommerceOverrides {

	protected $plugin;

	public function __construct( WP_Auth0 $plugin ) {
		$this->plugin = $plugin;
	}

	public function init() {
		add_filter( 'woocommerce_checkout_login_message', array( $this, 'override_woocommerce_checkout_login_form' ) );
		add_filter( 'woocommerce_before_customer_login_form', array( $this, 'override_woocommerce_login_form' ) );
	}

	public function override_woocommerce_checkout_login_form( $html ) {
		$this->override_woocommerce_login_form( $html );

		if ( isset( $_GET['wle'] ) ) {
			echo "<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>";
		}
	}

	public function override_woocommerce_login_form( $html ) {
		$this->plugin->render_auth0_login_css();
		echo $this->plugin->shortcode( array() );
	}

}
