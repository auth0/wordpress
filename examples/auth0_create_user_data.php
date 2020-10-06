<?php

/**
 * Modify the user data parsed from the Auth0 profile before being passed to wp_insert_user().
 *
 * @param array $user_data - User data parsed from the Auth0 profile.
 * @param object $userinfo - User profile from Auth0.
 *
 * @return array
 */
function example_auth0_create_user_data( array $user_data, $userinfo ) {
	$userinfo = (array) $userinfo;

	// Look for a custom username claim and set user_login if present.
	if ( ! empty( $userinfo['https://example.com/username'] ) ) {
		$user_data['user_login'] = $userinfo['https://example.com/username'];
	}

	return $user_data;
}
add_action( 'auth0_create_user_data', 'example_auth0_create_user_data', 10, 2 );
