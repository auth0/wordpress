<div class="a0-wrap">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>
  <?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/steps.php'; ?>

  <div class="container-fluid">
    <div class="row">

      <h1><?php _e( "Choose your password", "wp-auth0" ); ?></h1>

      <p class="a0-step-text"><?php _e( "Last step: Auth0 will migrate your own account from the WordPress user database to Auth0. You can choose to use the same password as you currently use, or pick a new one. Either way, Auth0 will link your existing account and its administrative role with the new account in Auth0. Type the password you wish to use for this account below.", "wp-auth0" ); ?></p>

      <?php if ( $error ) { ?>

      <p class="bg-danger">

        <?php _e( "An error occurred creating the user. Check that the migration webservices are accessible or check the ", "wp-auth0" ); ?>
        <a href="<?php echo admin_url( "admin.php?page=wpa0-errors" ); ?>" target="_blank"><?php _e( "Error Log", "wp-auth0" ); ?></a>
        <?php _e( "for more info.", "wp-auth0" ); ?>
      </p>

      <?php } ?>


      <form action="options.php" method="POST" onsubmit="onNext()">

        <div class="row">
          <div class="a0-admin-creation col-sm-6 col-xs-10">
            <input type="text" id="admin-email" value="<?php echo $current_user->user_email; ?>" disabled>
            <input type="password" id="admin-password" name="admin-password" placeholder="Password" value="" required>
          </div>
        </div>

        <div class="a0-buttons">
          <input type="hidden" name="action" value="wpauth0_callback_step3_social" />
          <input type="submit" class="a0-button primary" value="Submit" />
          <a onclick="onSkip()" href="<?php echo admin_url( 'admin.php?page=wpa0-setup&step=4&profile=social' ); ?>"class="a0-button link"><?php _e( "Skip this step", "wp-auth0" ); ?></a>
        </div>

      </form>

    </div>
  </div>
</div>
