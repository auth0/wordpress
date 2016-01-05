<div class="a0-wrap">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <div class="container-fluid">

  	<h1><?php _e('Import/Export Settings', WPA0_LANG); ?></h1>

    <div class="row">
  	 
      <div class="col-sm-6">

        <form action="options.php" method="post" onsubmit="return presubmit_import();" enctype="multipart/form-data">
          <input type="hidden" name="action" value="wpauth0_import_settings" />
    			
          <h2>Import settings</h2>
    			
          <div>Upload a file:</div>
          <div><input type="file" name="settings-file" /></div>
          <div>or copy the json</div>
    			<div><textarea name="settings-json"></textarea></div>

          <div class="a0-buttons">          
            <input type="submit" name="setup" class="a0-button primary" value="Import" />
          </div>

    		</form>

      </div>

      <div class="col-sm-6">

        <form action="options.php" method="post" onsubmit="return presubmit_export();">
          <input type="hidden" name="action" value="wpauth0_export_settings" />

    			<h2>Export settings</h2>

          <div class="a0-buttons">          
            <input type="submit" name="setup" class="a0-button primary" value="Export" />
          </div>

    		</form>

      </div>

  	</div>

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
