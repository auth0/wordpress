<?php
$current_user = get_currentauth0user();
if ( empty( $current_user->auth0_obj ) ) {
  return;
}

$logout_url = wp_logout_url();
$logout_url = html_entity_decode( $logout_url );
$logout_url = add_query_arg( 'redirect_to', get_permalink(), $logout_url );
$logout_url = add_query_arg( 'SLO', 1, $logout_url );
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

    var sloOptions = { 'responseType' : 'token id_token', 'redirectUri' : '<?php echo home_url() ?>' };
    webAuth.checkSession( sloOptions, function ( err ) {
            if ( err && err.error && 'login_required' === err.error ) {
                window.location = '<?php echo $logout_url ?>';
            }
        }
    );
  });
})();
</script>
