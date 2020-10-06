<?php
/**
 * Prefix nonce cookie name.
 *
 * @param string $cookie_name - Cookie name to modify.
 *
 * @return string
 */
function example_auth0_nonce_cookie_name( $cookie_name ) {
	return 'STYXKEY_' . $cookie_name;
}
 add_filter( 'auth0_nonce_cookie_name', 'example_auth0_nonce_cookie_name' );
