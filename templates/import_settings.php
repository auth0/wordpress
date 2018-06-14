<div class="a0-wrap settings">

  <?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

  <div class="container-fluid">

    <div class="row">
      <h1><?php _e( 'Import and Export Settings', 'wp-auth0' ); ?></h1>
      <p class="a0-step-text top-margin no-bottom-margin">You can import and export your Auth0 WordPress plugin settings here. This allows you to either backup the data, or to move your settings to a new WordPress instance.</p>
    </div>
    <div class="row">

      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#import" aria-controls="import" role="tab" data-toggle="tab" class="js-a0-import-export-tabs">Import Settings</a></li>
        <li role="presentation"><a href="#export" aria-controls="export" role="tab" data-toggle="tab" class="js-a0-import-export-tabs">Export Settings</a></li>
      </ul>

      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="import">

          <form action="options.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="wpauth0_import_settings" />

            <div id="js-a0-upload-file">
              <p class="a0-step-text top-margin">Please upload the exported json file or <span class="link js-a0-upload-toggle">paste the entire json</span>.</p>
              <div class="a0-step-text top-margin"><input type="file" name="settings-file" /></div>
            </div>
            <div id="js-a0-paste-json" style="display:none;">
              <p class="a0-step-text top-margin">Please paste the exported json file or <span class="link js-a0-upload-toggle">upload the exported file</span>.</p>
              <div class="a0-step-text top-margin"><textarea name="settings-json"></textarea></div>
            </div>

            <div class="a0-buttons">
              <input type="submit" name="setup" class="a0-button primary" value="Import" />
            </div>

          </form>

        </div>
        <div role="tabpanel" class="tab-pane" id="export">

          <form action="options.php" method="post">
            <input type="hidden" name="action" value="wpauth0_export_settings" />

            <p class="a0-step-text top-margin">Download the entire plugin configuration.</p>

            <div class="a0-buttons">
              <input type="submit" name="setup" class="a0-button primary" value="Export" />
            </div>

          </form>

        </div>
      </div>

  	</div>

  </div>

</div>