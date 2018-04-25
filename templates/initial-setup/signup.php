<div class="a0-wrap">

  <?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

  <div class="container-fluid">
    <div class="row">
      <h1><?php _e( "Sign Up with Auth0", "wp-auth0" ); ?></h1>
      <p class="a0-step-text"><?php _e( "To get started with the plugin we need you to sign up with Auth0 below. It should only take a second:", "wp-auth0" ); ?></p>
    </div>
  </div>
</div>
<div id="lock-wrapper"></div>

<script type="text/javascript">
var lock = new Auth0Lock('zEYfpoFzUMEzilhkHilcWoNkrFfJ3hAI', 'auth0.auth0.com');

lock.showSignup({
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
