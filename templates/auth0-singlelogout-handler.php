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

    webAuth.client.getSSOData(function(err, data) {
      if (!err && ( !data.sso || uuids != data.lastUsedUserID)) {
        window.location = '<?php echo html_entity_decode( $logout_url ); ?>';
      }
    });
  });

})();
</script>
