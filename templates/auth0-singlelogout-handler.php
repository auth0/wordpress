<?php
$current_user = get_currentauth0user();
if ( empty( $current_user->auth0_obj ) ) {
  return;
}
?>
<script id="auth0" src="<?php echo esc_url( $this->a0_options->get('auth0js-cdn') ) ?>"></script>
<script type="text/javascript">
(function(){

  document.addEventListener("DOMContentLoaded", function() {
    if (typeof(auth0) === 'undefined') {
      return;
    }

    var webAuth = new auth0.WebAuth({
      clientID:'<?php echo sanitize_text_field( $this->a0_options->get( 'client_id' ) ); ?>',
      domain:'<?php echo sanitize_text_field( $this->a0_options->get( 'domain' ) ); ?>'
    });

    webAuth.checkSession( { 'responseType' : 'token', 'redirectUri' : window.location.href }, function ( err ) {
            if ( err && err.error ==='login_required' ) {
                window.location = '<?php echo esc_url( wp_logout_url( get_permalink() ) . '&SLO=1' ); ?>';
            }
        }
    );
  });
})();
</script>
