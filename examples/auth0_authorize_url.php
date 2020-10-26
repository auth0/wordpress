<?php
/**
 * Adjust the authorize URL before redirecting.
 *
 * @param string $auth_url - Built authorize URL.
 * @param array  $auth_params - Existing URL parameters.
 *
 * @return string
 */
function example_auth0_authorize_url( $auth_url, $auth_params ) {

	if ( 'twitter' === $auth_params['connection'] ) {
		$auth_url .= '&param1=value1';
	}

	if ( ! empty( $auth_params['display'] ) ) {
		$auth_url .= '&param2=value2';
	}

	$auth_url .= '&param3=value3';
	return $auth_url;
}
 add_filter( 'auth0_authorize_url', 'example_auth0_authorize_url', 10, 2 );
