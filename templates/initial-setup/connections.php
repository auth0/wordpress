<div class="a0-wrap">

	<?php
require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php';

if ( !$migration_ws_enabled ) {
	require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/steps.php';
}
?>

	<div class="container-fluid">
		<div class="row">

			<h1><?php _e( "Configure your social connections", "wp-auth0" ); ?></h1>

			<p class="a0-step-text"><?php _e( "If your site visitors already have social network accounts, they can authenticate using their existing credentials, or they can set up a username and password combination safeguarded by Auth0's password policies and brute force protection. To configure these connections, use the Auth0 Dashboard button below.", "wp-auth0" ); ?></p>

			<div class="a0-separator"></div>

		</div>

		<div class="row">
			<div class="a0-buttons">
			<a onclick="gotodashboard()" href="https://manage.auth0.com/#/applications/<?php echo $client_id; ?>/connections" class="a0-button primary" target="_blank"><?php _e( "Auth0 Dashboard", "wp-auth0" ); ?></a>
			<a onclick="onNext()" href="<?php echo admin_url( "admin.php?page=wpa0-setup&step={$next_step}&profile=social" ); ?>" class="a0-button primary">Next</a>
		</div>
    </div>

	</div>
</div>
