<?php
/**
 * Should a new user from Auth0 be created?
 *
 * @param bool     $should_create - should the user be created, initialized as TRUE
 * @param stdClass $userinfo      - Auth0 user information
 *
 * @return bool
 */
function example_wpa0_should_create_user( $should_create, $userinfo ) {
	$should_create = false !== strpos( '@example.com', $userinfo->email );
	return $should_create;
}
add_filter( 'wpa0_should_create_user', 'example_wpa0_should_create_user' );
