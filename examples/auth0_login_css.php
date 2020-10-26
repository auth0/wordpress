<?php

/**
 * Add CSS to the Auth0 login form.
 *
 * @param string $css - initialized as empty.
 *
 * @return string
 */
function example_auth0_login_css( $css ) {
	$css .= '
		body {background: radial-gradient(#01B48F, #16214D)} 
		#login h1 {display: none}
		.login form.auth0-lock-widget {box-shadow: none}
	';
	return $css;
}
add_filter( 'auth0_login_css', 'example_auth0_login_css' );
