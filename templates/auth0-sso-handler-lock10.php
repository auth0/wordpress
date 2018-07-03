<?php
$lock_options = new WP_Auth0_Lock10_Options();
$client_id    = $lock_options->get_client_id();
$domain       = $lock_options->get_domain();
?>
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
  webAuth.checkSession(options, function (err, authResult) {
	  if (typeof(authResult) === 'undefined') {
		return;
	  }

	  if (authResult.idToken) {
		jQuery(document).ready(function($){
		  var $form=$(document.createElement('form')).css({display:'none'}).attr("method","POST").attr("action","
			<?php
			echo add_query_arg( 'auth0', 'implicit', site_url( 'index.php' ) );
			?>
			");
		  var $input=$(document.createElement('input')).attr('name','token').val(authResult.idToken);
		  var $input2=$(document.createElement('input')).attr('name','state').val(authResult.state);
		  $form.append($input).append($input2);
		  $("body").append($form);
		  Cookies.set( '<?php echo WP_Auth0_State_Handler::get_storage_cookie_name(); ?>', authResult.state );
		  Cookies.set( '<?php echo WP_Auth0_Nonce_Handler::get_storage_cookie_name(); ?>', authResult.idTokenPayload.nonce );
		  $form.submit();
		});
	  }
	});

});
</script>
