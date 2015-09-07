<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("Auth0 for WordPress - Quick Start Guide (step $step)", WPA0_LANG); ?></h2>

	<form action="options.php" method="POST">

		<input type="checkbox" name="migration_ws" id="wpa0_auth0_migration_ws" value="1" <?php echo checked( $migration_ws, 1, false ); ?>/>
		<div class="subelement">
			<span class="description"><?php echo __( 'Mark this to expose a WS in order to easy the users migration process.', WPA0_LANG ); ?></span>
			<span class="description"><?php echo __( 'Security token:', WPA0_LANG ); ?><code><?php echo $token; ?></code></span>

			<input type="hidden" name="action" value="wpauth0_callback_step2" />
			<input type="hidden" name="migration_token" value="<?php echo $token; ?>" />
			<input type="hidden" name="migration_token_id" value="<?php echo $token_id; ?>" />

			<p>
				This action will create a new Database connection with the custom scrits required to import your Wordpress Users.
			</p>

			<input type="submit" value="Next" name="next"/>
		</div>

	</form>

</div>
