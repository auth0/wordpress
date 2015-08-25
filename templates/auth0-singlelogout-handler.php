<script id="auth0" src="<?php echo $cdn ?>"></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
  var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');
  lock.$auth0.getSSOData(function(err, data) {
      if (!err && !data.sso) {
          window.location = '<?php echo wp_logout_url(get_permalink()); ?>';
      }
  });
});
</script>
