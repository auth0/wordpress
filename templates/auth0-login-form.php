<?php
$client_id = WP_Auth0_Options::get('client_id');
$domain = WP_Auth0_Options::get('domain');
$show_icon = absint(WP_Auth0_Options::get('show_icon'));
$cdn = WP_Auth0_Options::get('cdn_url');
$allow_signup = WP_Auth0_Options::get('allow_signup') == 1;
$extra_css = apply_filters( 'auth0_login_css', '');
$activated = absint(WP_Auth0_Options::get( 'active' )) == 1;

$dict = WP_Auth0_Options::get('dict');
$social_big_buttons = WP_Auth0_Options::get('social_big_buttons') == 1;

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
        <?php if ($wordpress_login_enabled && $activated): ?>
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

    var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');
    var options = {
        callbackURL:    '<?php echo site_url('/index.php?auth0=1'); ?>',
        container:      'auth0-login-form',
        authParams: {
            state:      '<?php echo $state; ?>'
        },
        dict: {
            signin: {
                title: '<?php echo $title ?>'
            }
        },
        socialBigButtons: <?php echo ($social_big_buttons ? 'true' : 'false') ;?>
    };

    <?php if ($show_icon) { ?>
        options['icon'] = WP_Auth0_Options::get('icon_url');
    <?php } ?>

    <?php if ($allow_signup) { ?>
        lock.show(options, callback);
    <?php } else { ?>
        lock.showSignin(options, callback);
    <?php } ?>




</script>
<?php
endif;