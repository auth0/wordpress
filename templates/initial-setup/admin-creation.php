<div class="a0-wrap">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>
  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/steps.php'); ?>

  <div class="container-fluid">

    <h1><?php _e("Choose your password", WPA0_LANG); ?></h1>

    <p class="a0-step-text"><?php _e("Last step: Auth0 will migrate your own account from the WordPress user database to Auth0. You can choose to use the same password as you currently use, or pick a new one. Either way, Auth0 will link your existing account and its administrative role with the new account in Auth0. Type the password you wish to use for this account below.", WPA0_LANG); ?></p>

    <div class="row">
      <div class="a0-admin-creation col-md-4">
        <input type="text" id="admin-email" value="<?php echo $current_user->user_email; ?>" disabled />
        <input type="password" id="admin-password" name="admin-password" placeholder="Password" value="" />
      </div>
    </div>

  

  </div>
</div>