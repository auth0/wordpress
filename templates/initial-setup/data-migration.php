<div class="wrap">

	<h2><?php
	  // translators: the $step variable is a number representing the step that the setup wizard is on.
	  printf( __( 'Auth0 for WordPress - Setup Wizard (step %d)', 'wp-auth0' ), $step ); ?></h2>

	<p>
		<?php _e( 'This will create a new database connection, expose 2 endpoints, and populate the custom scripts to call this endpoints to migrate the users to Auth0.', 'wp-auth0' ); ?>
		<?php _e( 'The users will not be changed in WordPress.', 'wp-auth0' ); ?>
	</p>

	<form action="options.php" method="POST">

		<input type="checkbox" name="migration_ws" id="wpa0_auth0_migration_ws" value="1" <?php echo checked( $migration_ws, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'Mark this to expose a web service to handle the user migration process.', 'wp-auth0' ); ?></span>
			<span class="description"><?php echo __( 'Security token:', 'wp-auth0' ); ?><code><?php echo $token; ?></code></span>
			<p>
				<?php _e( 'This action will create a new Database connection with the custom scripts required to import your WordPress Users.', 'wp-auth0' ); ?>
			</p>
		</div>

		<input type="hidden" name="action" value="wpauth0_callback_step2" />
		<input type="hidden" name="migration_token" value="<?php echo $token; ?>" />
		<input type="hidden" name="migration_token_id" value="<?php echo $token_id; ?>" />

		<input type="submit" value="<?php _e( 'Next', 'wp-auth0' ); ?>" name="next"/>
	</form>

</div>
