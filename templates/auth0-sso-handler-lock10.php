<script id="auth0" src="<?php echo $cdn ?>"></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
  if (typeof(ignore_sso) !== 'undefined' && ignore_sso) {
    return;
  }
  if (typeof(Auth0Lock) === 'undefined') {
      return;
  }

  var auth0 = new Auth0({
    clientID:'<?php echo $client_id; ?>',
    domain:'<?php echo $domain; ?>'
  });
  auth0.getSSOData(function(err, data) {
      if (!err && data.sso) {
          auth0.signin(<?php echo json_encode( $lock_options->get_sso_options() ); ?>);
      }
  });
});
</script>
