<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Export Auth0 Users', WPA0_LANG); ?></h2>

    <form action="options.php" method="post">
		<input type="hidden" name="action" value="wpauth0_export_users" />
		<div class="text-alone">This action will export all your WordPress users that has an Auth0 account.</div>
		<div class="text-alone">Do you want to continue?</div>
		<div class="text-alone"><input type="submit" name="setup" value="Yes" class="button button-primary"/></div>
	</form>
</div>
