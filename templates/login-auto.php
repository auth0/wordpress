<?php
	if(is_user_logged_in())
		return;

	$client_id = WP_Auth0_Options::get('client_id');
	$domain = parse_url(WP_Auth0_Options::get('endpoint'), PHP_URL_HOST);
	$login_method = WP_Auth0_Options::get('auto_login_method');

	if(empty($login_method)): ?>
<div class="alert alert-error"><?php _e('Auth0 Auto Login Method not specified. Please do so, before using the Auth0 Auto Login functionality.',WPA0_LANG); ?></div>
<?php else: ?><script type="text/javascript">
	var auth0 = new Auth0({
		domain:       '<?php echo $domain; ?>',
		clientID:     '<?php echo $client_id; ?>',
		callbackURL:  '<?php echo site_url('/auth0/'); ?>'
	});
	auth0.login({
		connection: '<?php echo $login_method; ?>'
	});
</script>
<?php endif; ?>