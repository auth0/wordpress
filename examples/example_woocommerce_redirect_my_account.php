<?php
/**
 * Redirects from the WooCommerce My Account page to wp-login.php when a user is not logged in.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/template_redirect
 */
function example_woocommerce_redirect_my_account() {
	if ( is_user_logged_in() ) {
		return;
	}

	$my_account_pid = (int) get_option( 'woocommerce_myaccount_page_id' );
	if ( $my_account_pid && get_the_ID() === $my_account_pid ) {
		wp_safe_redirect( wp_login_url( get_permalink( $my_account_pid ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'example_woocommerce_redirect_my_account', 1 );
