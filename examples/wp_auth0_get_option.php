<?php
/**
 * Adjust an options value before use.
 *
 * @param mixed  $value - value of the option, initially pulled from the database.
 * @param string $key   - key of the settings array.
 *
 * @return mixed
 */
function example_wp_auth0_get_option( $value, $key ) {
	$value = 'bad_key' === $key ? 'That is a bad key and you know it' : $value;
	return $value;
}
add_filter( 'wp_auth0_get_option', 'example_wp_auth0_get_option', 10, 2 );
