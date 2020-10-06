<?php
/**
 * Adjust the authorize URL parameters used for auto-login and universal login page.
 *
 * @param array  $params - Existing URL parameters.
 * @param string $connection - Connection for auto-login, optional.
 * @param string $redirect_to - URL to redirect to after logging in.
 *
 * @return mixed
 */
function example_auth0_authorize_url_params( $params, $connection, $redirect_to ) {
	if ( 'twitter' === $connection ) {
		$params['param1'] = 'value1';
	}

	if ( false !== strpos( 'twitter', $redirect_to ) ) {
		$params['param2'] = 'value2';
	}

	return $params;
}
add_filter( 'auth0_authorize_url_params', 'example_auth0_authorize_url_params', 10, 3 );
