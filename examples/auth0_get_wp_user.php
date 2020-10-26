<?php

/**
 * Filter the WordPress user found during login.
 *
 * @see WP_Auth0_LoginManager::login_user()
 *
 * @param WP_User|null $user     - found WordPress user, null if no user was found.
 * @param stdClass     $userinfo - user information from Auth0.
 *
 * @return WP_User|null
 */
function example_auth0_get_wp_user( $user, $userinfo ) {
	$found_user = get_user_by( 'email', $userinfo->email );
	$user       = $found_user instanceof WP_User ? $user : null;
	return $user;
}
 add_filter( 'auth0_get_wp_user', 'example_auth0_get_wp_user', 1, 2 );
