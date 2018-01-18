<div class="a0-wrap">

  <?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

  <div class="container-fluid">
    <div class="row">
      <h1><?php _e( "Step 1: Choose your account type", "wp-auth0" ); ?></h1>
      <p class="a0-step-text"><?php _e( "Users can log in within their own credentials - social like Google or Facebook, or name/password -  or use their employee credentials through an enterprise connection. Use either or both, and you'll increase your WordPress site's security and gather data about your visitors. For more information and help on the Auth0 WordPress plugin please visit", "wp-auth0" ); ?> <a href="https://auth0.com/docs/cms" target="_blank">our help documentation</a> </p>
    </div>
    <div class="row">
      <div class="a0-step-text a0-message a0-warning">

        <b>Important:</b>
        <?php _e( 'To continue you need an Auth0 account, don\'t have one yet?', 'wp-auth0' ); ?>

        <a class="a0-button default pull-right" target="_blank" href="https://auth0.com/signup" >Sign up for free</a>

      </div>
    </div>
    <div class="a0-profiles row">
      <form action="options.php" method="POST" id="profile-form">

        <input type="hidden" name="action" value="wpauth0_callback_step1" />

        <div class="col col-sm-6">
          <div class="profile">
            <img src="<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/simple-end-users-login.svg">

            <h2><?php _e( "Social Login", "wp-auth0" ); ?></h2>

            <p><?php _e( "Let your users login with their social accounts. For example; Google, Facebook, Twitter and many more along with traditional username and password. Don't worry - if you have existing users they will still be able to login.", "wp-auth0" ); ?></p>

            <div class="a0-buttons">
              <input type="submit" value="Social" name="type" class="a0-button primary"/>
            </div>
          </div>
        </div>

        <div class="col col-sm-6">
          <div class="profile">
            <img src="<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/effortless-employee-access.svg">

            <h2><?php _e( "Enterprise Login", "wp-auth0" ); ?></h2>

            <p><?php _e( "Secure this WordPress instance with your organizations login system so that users can login with their work account information. For example, you can connect to your existing ActiveDirectory infrastructure.", "wp-auth0" ); ?></p>

            <div class="a0-buttons">
              <input type="submit" value="Enterprise" name="type" class="a0-button primary"/>
            </div>
          </div>
        </div>

        <input type="hidden" value="" name="profile-type" id="profile-type"/>

        <div class="modal fade" id="connectionSelectedModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="connectionSelectedModalLabel">Important</h4>
              </div>
              <div class="modal-body no-padding-bottom">
                <p><?php _e( 'This wizard gets you started with the Auth0 for WordPress plug-in. You\'ll be transferred to Auth0 and can login or sign-up. Then you\'ll authorize the plug-in and configure identity providers, whether social or enterprise connections.', 'wp-auth0' ); ?></p>
                <p><b><?php _e( 'This plug-in replaces the standard WordPress login screen, but don\'t worry, you can still use your existing WordPress login. Auth0 adds many features to make login easier and better for your users but the old system will still be there too.', 'wp-auth0' ); ?></b></p>

                <div class="a0-message a0-warning multiline">

                  <b>Note:</b>
                  <?php _e( 'For this plugin to work, your server/host needs an inbound connection from auth0.com, as Auth0 needs to fetch some information to complete the process. If this website is not accesible from the internet, it will require manual intervention to configure the api token.', 'wp-auth0' ); ?>

                </div>

              </div>
              <div class="modal-footer">
                <a class="a0-button primary" href="#" id="manuallySetToken">Manual Setup (no Internet access)</a>
                <a class="a0-button primary submit" href="#">Automatic setup</a>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="enterTokenModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="enterTokenModalModalLabel">Important</h4>
              </div>
              <div class="modal-body">
                <p>
                  <?php _e( 'To complete the plugin\'s initial setup, you will need to enter your account subdomain:', 'wp-auth0' ); ?>
                </p>
                <input type="text" name="domain" placeholder="youraccount.auth0.com" />
                <br><br>
                <p>
                  <?php _e( 'And manually create an api token on the', 'wp-auth0' ); ?>
                  <a href="https://auth0.com/docs/api/v2" target="_blank"><?php echo __( 'token generator', 'wp-auth0' ); ?></a>
                  <?php _e( ' and paste it here:', 'wp-auth0' ); ?>
                </p>
                <input type="text" name="apitoken" autocomplete="off" />
                <p>
                  <small>
                    Scopes required:
                    <?php $a = 0; foreach ( $scopes as $resource => $actions ) { $a++;?>
                      <code><?php echo $actions ?> <?php echo $resource ?></code>
                      <?php
	if ( $a < count( $scopes ) - 1 ) {
		echo ", ";
	} else if ( $a === count( $scopes ) - 1 ) {
			echo " and ";
		}
?>
                    <?php } ?>.
                  </small>
                </p>
              </div>
              <div class="modal-footer">
                <input type="submit" class="a0-button primary" value="Continue"/>
              </div>
            </div>
          </div>
        </div>

      </form>
    </div>

    <div class="a0-message a0-tip row">
      <b><?php echo _e( 'Pro Tip' ); ?>:</b>
      <?php echo _e( 'Already set up another WordPress instance with Auth0? ' ); ?>
      <a href="admin.php?page=wpa0-import-settings"><?php echo _e( 'Click here' ); ?></a>
      <?php echo _e( ' to save time and import that site\'s SSO settings.' ); ?>
    </div>

  </div>
</div>


<script type="text/javascript">
  var with_token = false;

  var force_automatic = false;
  var force_manual = false;

  document.addEventListener("DOMContentLoaded", function() {
    metricsTrack('initial-setup:step1:open');

    var url = 'https://sandbox.it.auth0.com/api/run/wptest/wp-auth0-ping?domain=<?php echo urlencode( get_bloginfo( 'url' ) ); ?>';

    jQuery.ajax(url).done(function(response) {
      force_automatic = true;
      metricsTrack('initial-setup:step1:ping:automatic');
    }).fail(function( jqXHR, textStatus ) {
      metricsTrack('initial-setup:step1:ping:manual');
    });

  });

  jQuery('.a0-button.submit').click(function(e){
    e.preventDefault();
    metricsTrack('initial-setup:step1:' + jQuery('#profile-type').val() + ":" + (with_token ? 'token' : 'consent'), function() {
      jQuery('#profile-form').submit();
    } );
  });

  jQuery('.profile .a0-button').click(function(e){
    e.preventDefault();
    jQuery('#profile-type').val(jQuery(this).val());

    if (force_automatic && !force_manual) {
      jQuery('.a0-button.submit').click();
    } else if (force_manual && !force_automatic) {
      jQuery('#manuallySetToken').click();
    } else {
      jQuery('#connectionSelectedModal').modal();
    }

    return false;
  });
  jQuery('#manuallySetToken').click(function(e){
    e.preventDefault();
    with_token = true;
    jQuery('#enterTokenModal').modal();
    jQuery('#connectionSelectedModal').modal('hide');
    return false;
  });
</script>
