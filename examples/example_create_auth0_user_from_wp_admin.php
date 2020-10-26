<?php
// TODO: Determine your risk tolerance here with respect to accepting POST data below.
// phpcs:disable WordPress.Security
/**
 * Adds an Auth0 user when a new user is created in the WP-Admin.
 * This will NOT add a new user when one is registered via the WP form (Auth0 should handle that).
 * Make sure to change $payload['connection'] to the database connection you're using.
 *
 * @param int|WP_Error $wp_user_id ID of the newly created user.
 *
 * @return void|WP_Error
 */
function example_create_auth0_user_from_wp_admin( $wp_user_id ) {

	// WordPress user was not created so do not proceed.
	if ( is_wp_error( $wp_user_id ) ) {
		return;
	}

	$a0_options = WP_Auth0_Options::Instance();
	$payload    = [
		'client_id'  => $a0_options->get( 'client_id' ),
		// This is run during a POST request to create the user so pull the data from global.
		'email'      => $_POST['email'],
		'password'   => $_POST['pass1'],
		// TODO: Make sure this Database Connection is correct for your Auth0 configuration.
		'connection' => 'Username-Password-Authentication',
	];

	$new_auth0_user = WP_Auth0_Api_Client::signup_user( $a0_options->get( 'domain' ), $payload );

	// Returns false and logs an error in the plugin if this fails.
	// The WP user was still created but the Auth0 was not.
	if ( ! $new_auth0_user ) {
		return;
	}

	// Auth0 user created; now update the usermeta to connect the two accounts.
	$new_auth0_user->sub = 'auth0|' . $new_auth0_user->_id;
	unset( $new_auth0_user->_id );
	$user_repo = new WP_Auth0_UsersRepo( $a0_options );
	$user_repo->update_auth0_object( $wp_user_id, $new_auth0_user );
}
add_action( 'edit_user_created_user', 'example_create_auth0_user_from_wp_admin', 10 );
