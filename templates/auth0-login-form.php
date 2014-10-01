<?php
$client_id = WP_Auth0_Options::get('client_id');
$domain = WP_Auth0_Options::get('domain');
$show_icon = absint(WP_Auth0_Options::get('show_icon'));
$cdn = WP_Auth0_Options::get('cdn_url');
$allow_signup = WP_Auth0_Options::get('allow_signup') == 1;
$extra_css = apply_filters( 'auth0_login_css', '');
$dict = WP_Auth0_Options::get('dict');
$username_style = WP_Auth0_Options::get('username_style');
$social_big_buttons = WP_Auth0_Options::get('social_big_buttons') == 1;
$gravatar = WP_Auth0_Options::get('gravatar') == 1;
$remember_last_login = WP_Auth0_Options::get('remember_last_login') == 1;
$title = WP_Auth0_Options::get('form_title');
$extra_conf = WP_Auth0_Options::get('extra_conf');


if (isset($_GET['interim-login']) && $_GET['interim-login'] == 1) {
    $interim_login = true;
} else {
    $interim_login = false;
}

// Get title for login widget
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

    var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');

<?php

    if (trim($dict) == '')
    {
        $dict = array(
            "signin" => array(
                "title" => $title
            )
        );
    }

    $options_obj = array(
        "callbackURL"   =>  site_url('/index.php?auth0=1'),
        "container"     =>  'auth0-login-form',
        "authParams"    => array("state" => $state),
        "dict"          => $dict,
        "socialBigButtons"  => $social_big_buttons,
        "gravatar"          => $gravatar,
        "usernameStyle"     => $username_style,
        "rememberLastLogin" => $remember_last_login
    );

    if (trim($extra_conf) != '')
    {
        $extra_conf_arr = json_decode($extra_conf, true);
        $options_obj = array_merge( $extra_conf_arr, $options_obj  );
    }

    $options = json_encode($options_obj );
?>
    var options = <?php echo $options; ?>;

    <?php if ($allow_signup) { ?>
        lock.show(options, callback);
    <?php } else { ?>
        lock.showSignin(options, callback);
    <?php } ?>

</script>
<?php
endif;