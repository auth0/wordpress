<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Export Auth0 Users', WPA0_LANG); ?></h2>

	<div class="container">
	    <form action="options.php" method="post" onsubmit="return presubmit();">
			<input type="hidden" name="action" value="wpauth0_export_users" />
			<p>This action will export all your WordPress users that has an Auth0 account.</p>
			<p>Do you want to continue?</p>
			<div class="text-alone"><input type="submit" name="setup" value="Yes" class="button button-primary"/></div>
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
