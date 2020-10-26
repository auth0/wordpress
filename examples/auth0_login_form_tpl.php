<?php
/**
 * Override the Lock login form template.
 *
 * @param string  $tpl_path - original template path.
 * @param array   $lock_options - Lock options.
 * @param boolean $show_legacy_login - Should the template include a link to the standard WP login?
 *
 * @return string
 */
function example_auth0_login_form_tpl( $tpl_path, $lock_options, $show_legacy_login ) {
	return get_stylesheet_directory_uri() . '/templates/auth0-login-form.html';
}
 add_filter( 'auth0_login_form_tpl', 'example_auth0_login_form_tpl', 10, 3 );
