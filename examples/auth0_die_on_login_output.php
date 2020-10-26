<?php
/**
 * Filter the output of the wp_die() screen when the login callback fails.
 *
 * @see \WP_Auth0_LoginManager::die_on_login()
 *
 * @param string $html - Original HTML; modify and return or return something different.
 * @param string $msg - Error message.
 * @param string|integer $code - Error code.
 * @param boolean $login_link - True to link to login, false to link to logout.
 *
 * @return string
 */
function example_auth0_die_on_login_output( $html, $msg, $code, $login_link ) {
	return sprintf(
		'Original: <code style="display: block; background: #f1f1f1; padding: 1em; margin: 1em 0">%s</code>
		<strong>Message: </strong> %s<br><strong>Code: </strong> %s<br><strong>Link: </strong> <code>%s</code><br>',
		esc_html( $html ),
		sanitize_text_field( $msg ),
		sanitize_text_field( $code ),
		$login_link ? 'TRUE' : 'FALSE'
	);
}
add_filter( 'auth0_die_on_login_output', 'example_auth0_die_on_login_output', 10, 4 );
