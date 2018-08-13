<div class="a0-wrap">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

	<div class="container-fluid">

		<h1><?php _e( 'Export Auth0 Users', 'wp-auth0' ); ?></h1>

	   <form action="options.php" method="post">
			<input type="hidden" name="action" value="wpauth0_export_users" />
			<p class="a0-step-text">
				<?php _e( 'Download all your user information in a CSV file for manual processing.', 'wp-auth0' ); ?>
				<?php _e( 'The CSV will contain the users who logged in to this WordPress instance using Auth0.', 'wp-auth0' ); ?>
			</p>

			<div class="a0-buttons">
				<input type="submit" name="submit" id="submit" class="a0-button primary" value="<?php _e( 'Download CSV', 'wp-auth0' ); ?>" />
			</div>
		</form>

	</div>
</div>
