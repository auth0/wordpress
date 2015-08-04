<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 - App creation', WPA0_LANG); ?></h2>

	<div class="container">

	<?php if ($sucess) { ?>

		<p>Now, we need to create the application that the plugin will use. Enter a name for the application:</p>

	    <form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">

	        <input type="hidden" name="action" value="wpauth0_initialsetup_step2" />
	        <input type="text" name="app_name" value="<?php echo $name; ?>" autocomplete="off" />
			<input type="submit" name="contiue" class="button button-primary" value="Create">

	    </form>

	<?php } else { ?>

			<p>There was an error retriveing the token. Please <a href="<?php echo $consent_url; ?> ">try again</a>.</p>

	<?php } ?>

	</div>
</div>
