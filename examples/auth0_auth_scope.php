<?php
/**
 * Add or modify requested access token scopes during login.
 *
 * @param array $scopes - current array of scopes to add/delete/modify
 *
 * @return array
 */
function example_auth0_auth_scope( $scopes ) {
	// Add offline_access to include a refresh token.
	// See auth0_docs_hook_auth0_user_login() for how this token can be used.
	$scopes[] = 'offline_access';
	return $scopes;
}
 add_filter( 'auth0_auth_scope', 'example_auth0_auth_scope' );
