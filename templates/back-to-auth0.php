<?php
$title = wp_auth0_get_option( 'form_title' );
if ( empty( $title ) ) {
	$title = 'Auth0';
}
?>

<div id="extra-options">
	<a href="?">
	<?php
			// translators: The $title variable is the admin-controlled form title.
			printf( __( '← Back to %s login', 'wp-auth0' ), $title );
	?>
			</a>
</div>
