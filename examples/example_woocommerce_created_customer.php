<?php
/**
 * Adds an Auth0 user when a new customer is created in WooCommerce.
 * Make sure to change $payload['connection'] to the database connection you're using.
 *
 * @param integer $customer_id - WordPress user ID for the customer.
 * @param array   $new_customer_data - data used to create a new WordPress user.
 *
 * @link https://docs.woocommerce.com/wc-apidocs/source-function-wc_create_new_customer.html#114
 */
function example_woocommerce_created_customer( $customer_id, $new_customer_data ) {
	$a0_options     = WP_Auth0_Options::Instance();
	$payload        = [
		'client_id'  => $a0_options->get( 'client_id' ),
		'email'      => $new_customer_data['user_email'],
		'password'   => $new_customer_data['user_pass'],
		'connection' => 'Username-Password-Authentication',
	];
	$new_auth0_user = WP_Auth0_Api_Client::signup_user( $a0_options->get( 'domain' ), $payload );
	if ( $new_auth0_user ) {
		$new_auth0_user->sub = 'auth0|' . $new_auth0_user->_id;
		unset( $new_auth0_user->_id );
		$user_repo = new WP_Auth0_UsersRepo( $a0_options );
		$user_repo->update_auth0_object( $customer_id, $new_auth0_user );
	}
}
add_action( 'woocommerce_created_customer', 'example_woocommerce_created_customer', 10, 2 );
