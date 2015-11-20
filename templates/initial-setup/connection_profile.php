<div class="a0-wrap">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <h2><?php _e("Step 1: Choose your account type", WPA0_LANG); ?></h2>

  <p><?php _e("Users can log in witih their own credentials - social like Google or Facebook, or name/password -  or use their employee credentials through an enterprise connection. Use either or both, and you'll increase your WordPress site's security and gather data about your visitors.", WPA0_LANG); ?></p>

  <div class="a0-types">
    <form action="options.php" method="POST">

      <input type="hidden" name="action" value="wpauth0_callback_step2" />

      <div class="type">
        <h3><?php _e("Simple end-user logins.", WPA0_LANG); ?></h3>
        <p><?php _e("With this option, your users can log in with popular social accounts like Google or Facebook, or choose their own username and password.", WPA0_LANG); ?></p>
        
        <input type="submit" value="Social" name="type" class="a0-button primary"/>
      </div>

      <div class="type">
        <h3><?php _e("Effortless employee access.", WPA0_LANG); ?></h3>
        <p><?php _e("Let users log in with their work credentials by connecting your WordPress instance with your enterprise directory through Auth0. Auth0 will create a user record for each such user in a private database.", WPA0_LANG); ?></p>

        <input type="submit" value="Enterprise" name="type" class="a0-button primary"/>
      </div>

    </form>
  </div>

  <p class="a0-message a0-notice">
      <b><?php echo _e('Pro Tip'); ?>:</b>
      <?php echo _e('Already set up another WordPress instance with Auth0? Click here to save time and import that site\'s SSO settings.'); ?>
    </p>
</div> 
