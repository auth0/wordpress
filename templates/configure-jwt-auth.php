<div class="a0-wrap">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<div class="container-fluid">

		<h1><?php _e('JWT Auth authentication', WPA0_LANG); ?></h1>

		<?php if (!$ready) { ?>
			<form action="options.php" method="post">
				<input type="hidden" name="action" value="wpauth0_configure_jwt" />
				<p class="a0-step-text">This action will override the JWT Auth configuration and will enable it to authenticate users using the Auth0 JWT.<br>Do you want to continue?</p>

				<div class="a0-buttons">			    
					<input type="submit" name="submit" id="submit" class="a0-button primary" value="Yes" />
				</div>
			</form>
		<?php } else { ?>
			<p class="a0-step-text">JWT is configured and ready to work with Auth0 tokens.</p>
		<?php } ?>
	</div>
</div>
