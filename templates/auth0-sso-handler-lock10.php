<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
  if (typeof(ignore_sso) !== 'undefined' && ignore_sso) {
    return;
  }
  if (typeof(auth0) === 'undefined') {
    return;
  }

  var webAuth = new auth0.WebAuth({
    clientID:'<?php echo $client_id; ?>',
    domain:'<?php echo $domain; ?>'
  });

  var options = <?php echo json_encode( $lock_options->get_sso_options() ); ?>;
  webAuth.checkSession(options
  , function (err, authResult) {
    console.log(err);
    console.log(authResult);
  });

});
</script>
