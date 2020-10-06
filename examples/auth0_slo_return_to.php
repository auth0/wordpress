<?php
/**
 * URL to return to after logging out of Auth0.
 *
 * @param string $default_return_url - Return URL, default is home_url().
 *
 * @return string
 */
function example_auth0_slo_return_to( $default_return_url ) {
	$default_return_url = add_query_arg( 'cache-break', uniqid(), $default_return_url );
	return $default_return_url;
}
add_filter( 'auth0_slo_return_to', 'example_auth0_slo_return_to' );
