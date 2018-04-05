<?php
function renderAuth0Form( $canShowLegacyLogin = true, $specialSettings = array() ) {
	if ( is_user_logged_in() )
		return;

	if ( !$canShowLegacyLogin || !isset( $_GET['wle'] ) ) {
    $options = WP_Auth0_Options::Instance();

    if (!$options->get('passwordless_enabled')) {
      wp_enqueue_script( 'wpa0_lock', $options->get('cdn_url'), array( 'jquery' ), FALSE );
      require_once 'auth0-login-form-lock10.php';
    } else {
      wp_enqueue_script( 'wpa0_lock', $options->get('passwordless_cdn_url'), array( 'jquery' ), FALSE );
      require_once 'auth0-login-form.php';
    }
	} else {
		add_action( 'login_footer', array( 'WP_Auth0', 'render_back_to_auth0' ) );
		add_action( 'woocommerce_after_customer_login_form', array( 'WP_Auth0', 'render_back_to_auth0' ) );
	}
}