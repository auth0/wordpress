<?php
/**
 * URL used to logout of Auth0.
 *
 * @param string $default_logout_url - Logout URL.
 *
 * @return string
 */
function example_auth0_logout_url( $default_logout_url ) {
	$default_logout_url = add_query_arg( 'federated', 1, $default_logout_url );
	return $default_logout_url;
}
add_filter( 'auth0_logout_url', 'example_auth0_logout_url' );
