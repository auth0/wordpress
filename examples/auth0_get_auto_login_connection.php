<?php
/**
 * Filter the auto-login connection used by looking for a URL parameter.
 *
 * @param string $connection - name of the connection, initially pulled from Auth0 plugin settings.
 *
 * @return string mixed
 */
function example_auth0_get_auto_login_connection( ?string $connection ) {
	return ! empty( $_GET['connection'] ) ? rawurlencode( $_GET['connection'] ) : $connection;
}

add_filter( 'auth0_get_auto_login_connection', 'example_auth0_get_auto_login_connection' );
