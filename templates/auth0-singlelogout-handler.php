<script id="auth0" src="<?php echo $cdn ?>"></script>
<script type="text/javascript">
(function(){

  var uuids = '<?php echo $user_profile->user_id; ?>';
  document.addEventListener("DOMContentLoaded", function() {
    if (typeof(auth0) === 'undefined') {
      return;
    }

    var webAuth = new auth0.WebAuth({
      clientID:'<?php echo $client_id; ?>',
      domain:'<?php echo $domain; ?>'
    });

    var options = <?php echo json_encode( $lock_options->get_sso_options() ); ?>;
    options.responseType = 'token id_token';
    webAuth.checkSession(options, function (err, authResult) {
      if (err !== null) {
        if(err.error ==='login_required') {
          window.location = '<?php echo html_entity_decode( $logout_url ); ?>';
        }
      }
    });

  });
})();
</script>
