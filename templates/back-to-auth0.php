<?php
$title = WP_Auth0_Options::Instance()->get( 'form_title' );
if ( empty( $title ) ) {
	$title = 'Auth0';
}
?>

<style>
	#loginform,
	.woocommerce-account .woocommerce h2,
	.woocommerce-account .woocommerce form.login {
		display: block !important;
	}
</style>
<div id="extra-options">
	<a href="?"><?php printf( _e( '← Back to %s login', 'wp-auth0' ), $title ); ?></a>
</div>
