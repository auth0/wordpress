<?php
/**
 * Filter the JWT leeway.
 *
 * @param integer $leeway - Existing leeway time.
 *
 * @return integer
 */
function example_auth0_jwt_leeway( $leeway ) {
	return 90;
}
add_filter( 'auth0_jwt_leeway', 'example_auth0_jwt_leeway' );
