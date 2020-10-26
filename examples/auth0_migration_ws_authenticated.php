<?php
/**
 * Filter the WP user object before sending back to Auth0 during migration.
 *
 * @param WP_User $user - WordPress user object found during migration and authenticated.
 *
 * @return WP_User
 */
function example_auth0_migration_ws_authenticated( WP_User $user ) {
	$user->data->display_name = $user->data->display_name . ' The Great';
	return $user;
}
 add_filter( 'auth0_migration_ws_authenticated', 'example_auth0_migration_ws_authenticated' );
