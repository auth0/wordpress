<div class="a0-wrap">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<div class="container-fluid">

		<h1><?php _e('Export Auth0 Users', WPA0_LANG); ?></h1>

   	<form action="options.php" method="post" onsubmit="return presubmit();">
			<input type="hidden" name="action" value="wpauth0_export_users" />
			<p class="a0-step-text">This action will export all your WordPress users that has an Auth0 account.<br/>Do you want to continue?</p>

			<div class="a0-buttons">			    
				<input type="submit" name="submit" id="submit" class="a0-button primary" value="Yes" />
			</div>
		</form>

	</div>
</div>


<script type="text/javascript">
	function presubmit() {
		if (typeof(a0metricsLib) !== 'undefined') {
			a0metricsLib.track('bulk_export:users', {});
		}
		return true;
	}
</script>
