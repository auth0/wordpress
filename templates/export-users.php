<div class="a0-wrap">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<div class="container-fluid">

		<h1><?php _e('Export Auth0 Users', WPA0_LANG); ?></h1>

   	<form action="options.php" method="post" onsubmit="return presubmit();">
			<input type="hidden" name="action" value="wpauth0_export_users" />
			<p class="a0-step-text">Download all your user information in a CSV file for manual processing. The CSV will contain the users who logged in to this WordPress instance using Auth0.</p>

			<div class="a0-buttons">			    
				<input type="submit" name="submit" id="submit" class="a0-button primary" value="Download CSV" />
			</div>
		</form>

	</div>
</div>


<script type="text/javascript">
	function presubmit() {
		metricsTrack('bulk_export:users');
		return true;
	}
</script>
