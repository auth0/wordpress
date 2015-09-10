<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Import/Export Settings', WPA0_LANG); ?></h2>

	<div class="container">

    <form action="options.php" method="post" onsubmit="return presubmit_import();">
			<input type="hidden" name="action" value="wpauth0_import_settings" />

			<textarea name="settings-json"></textarea>

			<div class="text-alone"><input type="submit" name="setup" value="Import" class="button button-primary"/></div>
		</form>

    <form action="options.php" method="post" onsubmit="return presubmit_export();">
			<input type="hidden" name="action" value="wpauth0_export_settings" />
			<div class="text-alone"><input type="submit" name="setup" value="Export" class="button button-primary"/></div>
		</form>
	</div>
</div>


<script type="text/javascript">
  function presubmit_import() {
    if (typeof(a0metricsLib) !== 'undefined') {
      a0metricsLib.track('import:settings', {});
    }
    return true;
  }
  function presubmit_export() {
    if (typeof(a0metricsLib) !== 'undefined') {
      a0metricsLib.track('export:settings', {});
    }
    return true;
  }
</script>
