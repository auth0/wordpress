<?php
/**
 * Add, modify, or remove state values on login redirect.
 *
 * @param array $state Current state array.
 * @param array $auth_params Authorization URL parameters.
 *
 * @return array
 */
function example_auth0_authorize_state( $state, $auth_params ) {
	$redirect_to = wp_parse_url( $state['redirect_to'] ?? '' );
	if ( '/checkout' === ( $redirect_to['path'] ?? '' ) ) {
		$state['cart_id'] = example_get_cart_id();
	}
	return $state;
}
add_filter( 'auth0_authorize_state', 'example_auth0_authorize_state', 10, 2 );

function example_get_cart_id() {
	// TODO: Implement
	return wp_rand( 1, 1000 );
}
