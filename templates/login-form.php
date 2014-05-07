<?php
if(is_user_logged_in())
    return;

$client_id = WP_Auth0_Options::get('client_id');
$domain = WP_Auth0_Options::get('domain');
$show_icon = absint(WP_Auth0_Options::get('show_icon'));
$wp_login = absint(WP_Auth0_Options::get('wp_login_form'));
$form_desc = WP_Auth0_Options::get('form_desc');
if (isset($_GET['interim-login']) && $_GET['interim-login'] == 1) {
    $interim_login = true;
} else {
    $interim_login = false;
}

$stateObj = array("interim" => $interim_login, "uuid" =>uniqid());
$state = $_SESSION['auth0_state'] = json_encode($stateObj);

if(empty($client_id) || empty($domain)): ?>
    <p><?php _e('Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', WPA0_LANG); ?></p>
<?php else: ?>
    <div id="form-signin-wrapper" class="auth0-login">
        <?php include 'error-msg.php'; ?>
        <div class="form-signin">
            <h2 class="form-signin-heading"><?php echo WP_Auth0_Options::get('form_title'); ?></h2>
            <?php if(!empty($form_desc)): ?>
            <p>
                <?php echo $form_desc; ?>
            </p>
            <?php endif; ?>
            <div id="auth0-login-form" style=" min-height: 440px;"></div>
            <?php if($wp_login): ?>
            <div id="wp-login-form-wrapper">
                <?php include 'wp-login-form.php'; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script id="auth0" src="https://d19p4zemcycm7a.cloudfront.net/w2/auth0-widget-2.4.min.js"></script>
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
            state:          '<?php echo $state; ?>'
        });

        widget.signin({
            onestep: true,
            theme: 'static',
            standalone: true,
            showIcon: <?php echo ($show_icon ? 'true' : 'false'); ?>,
            icon: '<?php echo ($show_icon ? WP_Auth0_Options::get('icon_url') : ''); ?>'
        }, callback);

    </script>
    <style type="text/css">
        #loginform{display: none;}
    </style>
<?php endif;