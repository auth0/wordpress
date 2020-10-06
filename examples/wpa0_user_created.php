<?php

/**
 * Stop the login process after a new user has been created.
 * NOTE: The example below will break the user login process.
 *
 * @see WP_Auth0_Users::create_user()
 *
 * @param integer $user_id  - WordPress user ID for created user
 * @param string  $email    - email address for created user
 * @param string  $password - password used for created user
 * @param string  $f_name   - first name for created user
 * @param string  $l_name   - last name for created user
 */
function example_wpa0_user_created( $user_id, $email, $password, $f_name, $l_name ) {
	echo '<strong>User ID</strong>:<br>' . $user_id . '<hr>';
	echo '<strong>Email</strong>:<br>' . $email . '<hr>';
	echo '<strong>Password</strong>:<br>' . $password . '<hr>';
	echo '<strong>First name</strong>:<br>' . $f_name . '<hr>';
	echo '<strong>Last name</strong>:<br>' . $l_name . '<hr>';
	wp_die( 'User created!' );
}
 add_action( 'wpa0_user_created', 'example_wpa0_user_created', 10, 5 );
