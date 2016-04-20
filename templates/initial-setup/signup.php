<div class="a0-wrap">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <div class="container-fluid">
    <div class="row">
      <h1><?php _e("Create your Auth0 account", WPA0_LANG); ?></h1>
      <p class="a0-step-text"><?php _e("Users can log in within their own credentials - social like Google or Facebook, or name/password -  or use their employee credentials through an enterprise connection. Use either or both, and you'll increase your WordPress site's security and gather data about your visitors. For more information and help on the Auth0 WordPress plugin please visit", WPA0_LANG); ?> <a href="https://auth0.com/docs/cms" target="_blank">our help documentation</a> </p>
    </div>
  </div>
</div>
<div id="lock-wrapper"></div>

<script type="text/javascript">

document.addEventListener("DOMContentLoaded", function() {

  metricsTrack('initial-setup:singup:open');

});

var lock = new Auth0Lock('zEYfpoFzUMEzilhkHilcWoNkrFfJ3hAI', 'auth0.auth0.com');

lock.showSignup({
  rememberLastLogin: true,
  integratedWindowsLogin: false,
  dict: {
    signup: {
      footerText: 'By signing up, you agree to our <a href="https://auth0.com/terms" target="_new">terms of service</a> and <a href="https://auth0.com/privacy" target="_new">privacy policy</a>'
    }
  },
  callbackURL: 'https://manage.auth0.com/callback',
  responseType: 'code',
  container:'lock-wrapper'
});

</script>