<?php
/**
 * Redirect users without a WordPress session to log in.
 */
function example_redirect_to_authenticate() {
	if ( is_user_logged_in() ) {
		// User is logged in, nothing to do.
		return;
	}

	if ( 'page-template-that-needs-auth.php' === get_page_template_slug() ) {
		// User is trying to access a page template that requires authentication.
		auth_redirect();
	}

	if ( 'post' === get_post_type() ) {
		// User is trying to access a post type that requires authentication.
		auth_redirect();
	}
}
 add_action( 'template_redirect', 'example_redirect_to_authenticate', 1 );

