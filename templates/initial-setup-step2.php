<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 - App creation', WPA0_LANG); ?></h2>

	<p>Now, we need to create the application that the plugin will use. Enter a name for the application:</p>

    <form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">

        <input type="hidden" name="action" value="wpauth0_initialsetup_step2" />
        <input type="text" name="app_name" value="<?php echo $name; ?>" autocomplete="off" />

		<p class="submit">
			<input type="submit" name="contiue" class="button button-primary" value="Create">
		</p>

    </form>

</div>
