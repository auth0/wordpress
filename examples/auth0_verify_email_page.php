<?php
/**
 * Filter the HTML used on the email verification wp_die page.
 *
 * @see WP_Auth0_Email_Verification::render_die()
 *
 * @param string   $html     - HTML to modify, echoed out within wp_die().
 * @param stdClass $userinfo - user info object from Auth0.
 * @return string
 */
function example_auth0_verify_email_page( $html, $userinfo ) {
	$html = 'Hi ' . $userinfo->email . '!<br>' . $html;
	$html = str_replace( 'email', 'banana', $html );
	return $html;
}
 add_filter( 'auth0_verify_email_page', 'example_auth0_verify_email_page', 10, 2 );
