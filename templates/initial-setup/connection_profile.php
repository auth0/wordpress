<div class="a0-wrap">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <div class="container-fluid">
    <h1><?php _e("Step 1: Choose your account type", WPA0_LANG); ?></h1>

    <p><?php _e("Users can log in witih their own credentials - social like Google or Facebook, or name/password -  or use their employee credentials through an enterprise connection. Use either or both, and you'll increase your WordPress site's security and gather data about your visitors.", WPA0_LANG); ?></p>

    <div class="a0-profiles row">
      <form action="options.php" method="POST">

        <input type="hidden" name="action" value="wpauth0_callback_step2" />

        <div class="col-md-6">
          <div class="profile">
            <h2><?php _e("Simple end-user logins.", WPA0_LANG); ?></h2>

            <img src="<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/simple-end-users-login.svg">

            <p><?php _e("With this option, your users can log in with popular social accounts like Google or Facebook, or choose their own username and password.", WPA0_LANG); ?></p>
            
            <div class="a0-buttons">
              <input type="submit" value="Social" name="type" class="a0-button primary"/>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="profile">
            <h2><?php _e("Effortless employee access.", WPA0_LANG); ?></h2>

            <img src="<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/effortless-employee-access.svg">

            <p><?php _e("Let users log in with their work credentials by connecting your WordPress instance with your enterprise directory through Auth0. Auth0 will create a user record for each such user in a private database.", WPA0_LANG); ?></p>

            <div class="a0-buttons">
              <input type="submit" value="Enterprise" name="type" class="a0-button primary"/>
            </div>
          </div>
        </div>

        <input type="hidden" value="" name="profile-type" id="profile-type"/>

        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
              </div>
              <div class="modal-body">
                <p><?php _e('This wizard gets you started with the Auth0 for WordPress plug-in. You\'ll be transferred to Auth0 and can login or sign-up. Then you\'ll authorize the plug-in and configure identity providers, whether social or enterprise connections.', WPA0_LANG); ?></p>
                <p><?php _e('Finally, you\'ll migrate your own WordPress administrator account to Auth0, ready to configure the plug-in through the WordPress dashboard.', WPA0_LANG); ?></p>
                <p><b><?php echo _e('This plug-in replaces the standard WordPress login screen. The experience is improved of course, but different.  By default, there is a link to the regular WordPress login screen should you need it.'); ?></b></p>
              </div>
              <div class="modal-footer">
                <input type="submit" class="a0-button primary" value="Continue"/>
              </div>
            </div>
          </div>
        </div>

      </form>
    </div>

    <p class="a0-message a0-tip">
      <b><?php echo _e('Pro Tip'); ?>:</b>
      <?php echo _e('Already set up another WordPress instance with Auth0? Click here to save time and import that site\'s SSO settings.'); ?>
    </p>

  </div> 
</div> 


<script type="text/javascript">
  
  jQuery('.profile .a0-button').click(function(e){
    e.preventDefault();
    jQuery('#profile-type').val(jQuery(this).val());
    jQuery('#myModal').modal();
    return false;
  });
</script>
