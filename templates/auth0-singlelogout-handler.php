<script id="auth0" src="<?php echo $cdn ?>"></script>
<script type="text/javascript">
(function(){

  var uuids = '<?php echo $user_profile->user_id; ?>';
  document.addEventListener("DOMContentLoaded", function() {
    var client = new Auth0({
      domain:       '<?php echo $domain; ?>',
      clientID:     '<?php echo $client_id; ?>',
      responseType: 'token'
    });

    client.getSSOData(function(err, data) {
      if (!err && ( !data.sso || uuids != data.lastUsedUserID) ) {

        window.location = '<?php echo html_entity_decode( $logout_url ); ?>';

      }
    });
  });

})();
</script>