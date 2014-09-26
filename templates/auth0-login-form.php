<?php
$client_id = WP_Auth0_Options::get('client_id');
$domain = WP_Auth0_Options::get('domain');
$show_icon = absint(WP_Auth0_Options::get('show_icon'));
$cdn = WP_Auth0_Options::get('cdn_url');
$allow_signup = WP_Auth0_Options::get('allow_signup') == 1;
$extra_css = apply_filters( 'auth0_login_css', '');

$form_desc = WP_Auth0_Options::get('form_desc');
if (isset($_GET['interim-login']) && $_GET['interim-login'] == 1) {
    $interim_login = true;
} else {
    $interim_login = false;
}

// Get title for login widget
$title = WP_Auth0_Options::get('form_title');
if (empty($title)) {
    $title = "Sign In";
}

$stateObj = array("interim" => $interim_login, "uuid" =>uniqid());
$state = json_encode($stateObj);

if(empty($client_id) || empty($domain)): ?>
<p><?php _e('Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', WPA0_LANG); ?></p>
<?php else: ?>
<div id="form-signin-wrapper" class="auth0-login">
    <?php include 'error-msg.php'; ?>
    <div class="form-signin">
        <div id="auth0-login-form">
        </div>
        <?php if ($wordpress_login_enabled && $canShowLegacyLogin): ?>
            <div id="extra-options">
                <a href="?wle">Login with WordPress username</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if (!empty($extra_css)): ?>
<style type="text/css">
    <?php echo $extra_css; ?>
</style>
<?php endif; ?>
<script id="auth0" src="<?php echo $cdn ?>"></script>
<script type="text/javascript">
    var callback = null;
    if(typeof(a0_wp_login) === "object"){
        callback = a0_wp_login.initialize
    }

    var widget = new Auth0Widget({
        domain:     '<?php echo $domain; ?>',
        chrome: true,
        clientID:       '<?php echo $client_id; ?>',
        callbackURL:    '<?php echo site_url('/index.php?auth0=1'); ?>',
        container:      'auth0-login-form',
        state:          '<?php echo $state; ?>',
        showSignup:     <?php echo $allow_signup?'true':'false' ?>,
        dict:           { signin: { title: '<?php echo $title ?>' } }
    });

    widget.signin({
        onestep: true,
        theme: 'static',
        standalone: true,
        showIcon: <?php echo ($show_icon ? 'true' : 'false'); ?>,
        icon: '<?php echo ($show_icon ? WP_Auth0_Options::get('icon_url') : ''); ?>'
    }, callback);

</script>
<?php
endif;