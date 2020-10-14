<?php
/**
 * Play nicely with Restricted Site Access.
 *
 * @param bool $is_restricted - Original $is_restricted value
 * @param WP   $wp - WP object.
 *
 * @return mixed
 */
function example_restricted_site_access_is_restricted( $is_restricted, $wp ) {
	if (
		! empty( $wp->query_vars['auth0'] )
		&& empty( $wp->query_vars['page'] )
		&& isset( $_COOKIE['auth0_state'] )
		&& $_COOKIE['auth0_state'] === $wp->query_vars['state']
	) {
		return false;
	}
	return $is_restricted;
}
add_filter( 'restricted_site_access_is_restricted', 'example_restricted_site_access_is_restricted', 100, 2 );

