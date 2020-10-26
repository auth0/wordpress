<?php
/**
 * Filter the max_age login parameter.
 *
 * @param integer $max_age - Existing max_age time, defaults to empty.
 *
 * @return integer
 */
function example_auth0_jwt_max_age( $max_age ) {
	return 1200;
}
add_filter( 'auth0_jwt_max_age', 'example_auth0_jwt_max_age' );
