<?php

/**
 * Stop login process before logging in and output the current $user object.
 * NOTE: The example below will break the user login process.
 *
 * @see WP_Auth0_LoginManager::do_login()
 *
 * @param WP_User $user - WordPress user object.
 */
function example_auth0_before_login( $user ) {
	echo '<strong>WP user</strong>:<br><pre>' . print_r( $user, true ) . '</pre><hr>';
	wp_die( 'Login process started!' );
}
 add_action( 'auth0_before_login', 'example_auth0_before_login', 10, 1 );
