<div class="a0-wrap">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

	<div class="container-fluid">

		<h1><?php _e( 'JWT Auth authentication', 'wp-auth0' ); ?></h1>

		<?php if ( ! $ready ) { ?>
			<form action="options.php" method="post">
				<input type="hidden" name="action" value="wpauth0_configure_jwt" />
				<p class="a0-step-text">
					<?php _e( 'This action will override the JWT Auth configuration and will enable it to authenticate users using the Auth0 JWT.', 'wp-auth0' ); ?>
					<br><?php _e( 'Do you want to continue?', 'wp-auth0' ); ?></p>

				<div class="a0-buttons">
					<input type="submit" name="submit" id="submit" class="a0-button primary" value="<?php _e( 'Yes', 'wp-auth0' ); ?>" />
				</div>
			</form>
		<?php } else { ?>
			<p class="a0-step-text"><?php _e( 'JWT is configured and ready to work with Auth0 tokens.', 'wp-auth0' ); ?></p>
		<?php } ?>
	</div>
</div>
