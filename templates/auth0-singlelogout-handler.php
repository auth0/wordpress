<?php
$current_user = get_currentauth0user();
$domain = $this->a0_options->get( 'domain' );
$client_id = $this->a0_options->get( 'client_id' );

if ( empty( $current_user->auth0_obj ) || empty( $domain ) || empty( $client_id ) ) {
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
      clientID:'<?php echo sanitize_text_field( $domain ); ?>',
      domain:'<?php echo sanitize_text_field( $client_id ); ?>'
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
