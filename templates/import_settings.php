<div class="a0-wrap settings">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <div class="container-fluid">

    <div class="row">
  	 
      <div class="col-sm-6">

        <form action="options.php" method="post" onsubmit="return presubmit_import();" enctype="multipart/form-data">
          <input type="hidden" name="action" value="wpauth0_import_settings" />
    			
          <h1><?php _e('Import Settings', WPA0_LANG); ?></h1>
    			
          <div id="upload-file">
            <p class="a0-step-text">Please upload the exported json file or <span class="link upload-toggle">paste the entire json</span>.</p>
            <input type="file" name="settings-file" />
          </div>
    			<div id="paste-json" style="display:none;">
            <p class="a0-step-text">Please paste the exported json file or <span class="link upload-toggle">upload the exported file</span>.</p>
            <textarea name="settings-json"></textarea>
          </div>

          <div class="a0-buttons">          
            <input type="submit" name="setup" class="a0-button primary" value="Import" />
          </div>

    		</form>

      </div>

      <div class="a0-separator hidden-sm hidden-md hidden-lg"></div>

      <div class="col-sm-6">

        <form action="options.php" method="post" onsubmit="return presubmit_export();">
          <input type="hidden" name="action" value="wpauth0_export_settings" />

          <h1><?php _e('Export Settings', WPA0_LANG); ?></h1>

          <p class="a0-step-text">Download the entire plugin configuration.</p>

          <div class="a0-buttons">          
            <input type="submit" name="setup" class="a0-button primary" value="Export" />
          </div>

    		</form>

      </div>

  	</div>

  </div>

</div>


<script type="text/javascript">
  jQuery('.upload-toggle').click(function(){

    jQuery('#upload-file').toggle();
    jQuery('#paste-json').toggle();

  });
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
