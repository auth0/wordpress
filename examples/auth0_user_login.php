<?php
/**
 * Stop the login process after WP login.
 * NOTE: The example below will break the user login process.
 *
 * @see WP_Auth0_LoginManager::do_login()
 *
 * @param integer  $user_id       - WordPress user ID for logged-in user
 * @param stdClass $userinfo      - user information object from Auth0
 * @param boolean  $is_new        - true if the user was created in WordPress, false if not
 * @param string   $id_token      - ID token for the user from Auth0
 * @param string   $access_token  - bearer access token from Auth0 (not used in implicit flow)
 * @param string   $refresh_token - refresh token from Auth0 (not used in implicit flow)
 */
function example_auth0_user_login( $user_id, $userinfo, $is_new, $id_token, $access_token, $refresh_token ) {
	echo '<strong>WP user ID</strong>:<br>' . $user_id . '<hr>';
	echo '<strong>Auth0 user info</strong>:<br><pre>' . print_r( $userinfo, true ) . '</pre><hr>';
	echo '<strong>Added to WP DB?</strong>:<br>' . ( $is_new ? 'yep' : 'nope' ) . '<hr>';
	echo '<strong>ID Token</strong>:<br>' . ( $id_token ? $id_token : 'not provided' ) . '<hr>';
	echo '<strong>Access Token</strong>:<br>' . ( $access_token ? $access_token : 'not provided' ) . '<hr>';
	echo '<strong>Refresh Token</strong>:<br>' . ( $refresh_token ? $refresh_token : 'not provided' ) . '<hr>';
	wp_die( 'Login successful! <a href="' . home_url() . '">Home</a>' );
}
add_action( 'auth0_user_login', 'example_auth0_user_login', 10, 6 );
