<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('JWT Auth authentication', WPA0_LANG); ?></h2>

	<div class="container">
	<?php if (!$ready) { ?>
		<form action="options.php" method="post">
			<input type="hidden" name="action" value="wpauth0_configure_jwt" />
			<p>This action will override the JWT Auth configuration and will enable it to authenticate users using the Auth0 JWT.</p>
			<p>Do you want to continue?</p>
			<p><input type="submit" name="setup" value="Yes" class="button button-primary"/></p>
		</form>
	<?php } else { ?>
		<p>JWT is configured and ready to work with Auth0 tokens.</p>
	<?php } ?>
	</div>
</div>
