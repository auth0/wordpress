<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 - App creation', WPA0_LANG); ?></h2>

    <form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
        <input type="hidden" name="action" value="wpauth0_initialsetup_step3" />
        <input type="text" name="app_name" value="" autocomplete="off" />
        <input type="submit" name="contiue" value="Continue" />
    </form>

</div>
