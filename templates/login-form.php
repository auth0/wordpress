<?php
function renderAuth0Form( $canShowLegacyLogin = true, $specialSettings = array() ) {
	if ( is_user_logged_in() )
		return;

	if ( !$canShowLegacyLogin || !isset( $_GET['wle'] ) ) {
    $options = WP_Auth0_Options::Instance();

    if ( ! $options->get('passwordless_enabled', FALSE) ) {
      wp_enqueue_script( 'wpa0_lock', $options->get('cdn_url'), array( 'jquery' ), FALSE );
      require_once 'auth0-login-form-lock10.php';
    } else {
      $lock_options = new WP_Auth0_Lock10_Options( $specialSettings );

      // This is output in the footer so it can run after wp_head
      wp_enqueue_script( 'wpa0_lock', $options->get('passwordless_cdn_url'), array( 'jquery' ), FALSE );
      wp_enqueue_script( 'auth0-pwl', WPA0_PLUGIN_JS_URL . 'login-pwl.js', array( 'jquery' ), WPA0_VERSION, TRUE );

      // Set required global var
      wp_localize_script(
        'auth0-pwl',
        'wpAuth0PwlGlobal',
        array(
          'i18n' => array(),
          'lock' => array(
            'options' => $lock_options->get_lock_options(),
            'ready' => WP_Auth0::ready(),
            'domain' => $options->get( 'domain' ),
            'clientId' => $options->get( 'client_id' ),
          ),
        )
      );

      require_once 'auth0-login-form-pwl.php';
    }
	} else {
		add_action( 'login_footer', array( 'WP_Auth0', 'render_back_to_auth0' ) );
		add_action( 'woocommerce_after_customer_login_form', array( 'WP_Auth0', 'render_back_to_auth0' ) );
	}
}