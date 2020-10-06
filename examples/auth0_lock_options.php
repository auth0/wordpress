<?php
/**
 * Filter the options passed to Lock.
 *
 * @param array $options - Existing options built from plugin and additional settings.
 *
 * @return array
 */
function example_auth0_lock_options( $options ) {
	if ( ! empty( $_GET['lock_language'] ) ) {
		$options['language'] = sanitize_title( $_GET['lock_language'] );
	}
	return $options;
}
add_filter( 'auth0_lock_options', 'example_auth0_lock_options' );
