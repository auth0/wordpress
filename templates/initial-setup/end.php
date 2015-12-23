<div class="a0-wrap">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <div class="a0-final-step">
    
    <h1><?php _e("Done! You finished this Quick Start", WPA0_LANG); ?></h1>
  </div>

  <div class="container-fluid">
    <p class="a0-step-text"><?php _e("Adjust the plug-in's settings from the WordPress dashboard, and visit Auth0's dashboard to change how users log in, add connections, enable multi-factor authentication, and more.", WPA0_LANG); ?></h1>

    <div class="a0-buttons">
      <a href="<?php echo admin_url('admin.php?page=wpa0'); ?>" class="a0-button primary">GO TO PLUG-IN SETTINGS</a>
    </div>
  </div>
</div>
