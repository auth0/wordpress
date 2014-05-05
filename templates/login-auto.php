<?php
    if(is_user_logged_in())
        return;

    $client_id = WP_Auth0_Options::get('client_id');
    $domain = WP_Auth0_Options::get('domain');
    $login_method = WP_Auth0_Options::get('auto_login_method');

    if(empty($login_method)): ?>
<div class="alert alert-error"><?php _e('Auth0 Auto Login Method not specified. Please do so, before using the Auth0 Auto Login functionality.',WPA0_LANG); ?></div>
<?php else: ?>
    <script id="auth0" src="<?php echo WPA0_PLUGIN_URL ?>/assets/js/auth0.min.js"></script>
    <script type="text/javascript">
    var auth0 = new Auth0({
        domain:       '<?php echo $domain; ?>',
        clientID:     '<?php echo $client_id; ?>',
        callbackURL:  '<?php echo site_url('/index.php?auth0=1'); ?>'
    });
    auth0.login({
        connection: '<?php echo $login_method; ?>'
    });
</script>
<?php endif; ?>