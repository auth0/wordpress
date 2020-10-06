<?php
/**
 * Prefix used for constant-based options.
 * NOTE: This must load before WP_Auth0::init() so it cannot be used in a theme.
 *
 * @param string $prefix - Constant prefix to modify.
 *
 * @return string
 */
function example_auth0_settings_constant_prefix( $prefix ) {
	// Replace the prefix with something else.
	// return 'AUTH_ENV_';
	// Prefix the prefix.
	return 'PREFIX_' . $prefix;
}
add_filter( 'auth0_settings_constant_prefix', 'example_auth0_settings_constant_prefix' );

