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
      if (typeof(authResult) === 'undefined') {
        return;
      }

      if (typeof(authResult.code) !== 'undefined') {
        window.location = '<?php echo add_query_arg( 'auth0', 1, site_url() ); ?>&code=' + authResult.code +
            '&state=' + authResult.state;
      } else if (typeof(authResult.idToken) !== 'undefined') {
        jQuery(document).ready(function($){
          var $form=$(document.createElement('form')).css({display:'none'}).attr("method","POST").attr("action","<?php
            echo add_query_arg( 'auth0', 'implicit', site_url() ); ?>");
          var $input=$(document.createElement('input')).attr('name','token').val(authResult.idToken);
          var $input2=$(document.createElement('input')).attr('name','state').val(authResult.state);
          $form.append($input).append($input2);
          $("body").append($form);
          $form.submit();
        });
      }
    });

});
</script>
