<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("Auth0 for WordPress - Quick Start Guide (step $step)", WPA0_LANG); ?></h2>

	<p>This will create a new database connections, expose 2 endpoints and will pupulate the custom scripts to call this endpoints to migrate the users to auth0. the users will not be changed in wordpress.</p>

	<form action="options.php" method="POST">


		<input type="hidden" name="action" value="wpauth0_callback_step5" />

		<input type="submit" value="Next" name="next"/>
	</form>

</div>
