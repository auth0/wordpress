<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e( "Auth0 for WordPress - Setup Wizard (step $step)", WPA0_LANG ); ?></h2>

	<p>This will create a new database connections, expose 2 endpoints and will pupulate the custom scripts to call this endpoints to migrate the users to auth0. the users will not be changed in wordpress.</p>

	<form action="options.php" method="POST">

		<input type="checkbox" name="migration_ws" id="wpa0_auth0_migration_ws" value="1" <?php echo checked( $migration_ws, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'Mark this to expose a WS in order to easy the users migration process.', WPA0_LANG ); ?></span>
			<span class="description"><?php echo __( 'Security token:', WPA0_LANG ); ?><code><?php echo $token; ?></code></span>
			<p>
				This action will create a new Database connection with the custom scrits required to import your Wordpress Users.
			</p>
		</div>

		<input type="hidden" name="action" value="wpauth0_callback_step2" />
		<input type="hidden" name="migration_token" value="<?php echo $token; ?>" />
		<input type="hidden" name="migration_token_id" value="<?php echo $token_id; ?>" />

		<input type="submit" value="Next" name="next"/>
	</form>

</div>
