<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("Auth0 for WordPress - Quick Start Guide (step $step)", WPA0_LANG); ?></h2>


	<p>We should explain here why we need this and what will happen. This will log authenticate it against auth0, and will create the user as admin in this wordpress instance. We need this to call later to the server with a token with the required scopes.</p>

	<div id="a0-lock-wrapper">

  </div>

  <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
      var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');
      lock.show({
        container: 'a0-lock-wrapper',
				callbackURL: 'http://vagrantpress.dev/index.php?auth0=1',
				responseType: 'code',
				authParams: {
						scope: "openid update:clients update:connections create:connections read:connections create:rules delete:rules update:users name email nickname email_verified identities",
						state: '<?php echo json_encode(array(
							'redirect_to' => admin_url( 'admin.php?page=wpa0-setup&step=4' )
						)); ?>'
				}
      });
    });
  </script>

</div>
