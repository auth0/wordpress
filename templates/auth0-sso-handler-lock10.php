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
  //  webAuth.authorize({"scope":"openid ","responseType":"code","redirectUri":"http:\/\/localhost\/index.php?auth0=1","state":"eyJpbnRlcmltIjpmYWxzZSwidXVpZCI6IjU5ZWIxMDE5YzJmZmMiLCJyZWRpcmVjdF90byI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvIiwic3RhdGUiOiJub25jZSJ9","nonce":"nonce"});
  // webAuth.client.getSSOData(function(err, data) {
  //   if (!err && data.sso) {
  //     webAuth.authorize(<?php echo json_encode( $lock_options->get_sso_options() ); ?>);
  //   }
  // });

  var options = {<?php echo $lock_options->get_sso_options(); ?> };

  webAuth.checkSession({
    scope: options.scope,
    redirectUri: options.redirectUri
  }, function (err, authResult) {
    console.log(err);
    console.log(authResult);
  });
});
</script>
