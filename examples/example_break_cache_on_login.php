<?php
/**
 * Append a cache-breaking parameter to login_url redirects.
 * This can solve issues with cached authentication redirects and aggressive page caching.
 *
 * @param string $login_url - original login URL.
 * @param string $redirect - where to redirect after successful login.
 *
 * @return string
 */
function example_break_cache_on_login( $login_url, $redirect ) {
	if ( ! empty( $redirect ) ) {
		$login_url = remove_query_arg( 'redirect_to', $login_url );
		$redirect  = add_query_arg( 'logged_in', 1, $redirect );
		$redirect  = rawurlencode( $redirect );
		$login_url = add_query_arg( 'redirect_to', $redirect, $login_url );
	}

	return $login_url;
}
 add_filter( 'login_url', 'example_break_cache_on_login', 10, 2 );
