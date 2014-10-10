<?php
$client_id = WP_Auth0_Options::get('client_id');

if (trim($client_id) == "") return;

$domain = WP_Auth0_Options::get('domain');
$cdn = WP_Auth0_Options::get('cdn_url');

$allow_signup = false;

if (WP_Auth0_Options::is_wp_registration_enabled())
{
    $allow_signup = WP_Auth0_Options::get('allow_signup') == 1;
}

$extra_css = apply_filters( 'auth0_login_css', '');
$showAsModal = (isset($specialSettings['show_as_modal']) && $specialSettings['show_as_modal'] == 1);
$modalTriggerName = 'Login';
if (isset($specialSettings['modal_trigger_name']) && $specialSettings['modal_trigger_name'] != '')
{
    $modalTriggerName = $specialSettings['modal_trigger_name'];
}

if (isset($specialSettings['show_as_modal'])) unset($specialSettings['show_as_modal']);
if (isset($specialSettings['modal_trigger_namemodal_trigger_name'])) unset($specialSettings['modal_trigger_name']);

$form_desc = WP_Auth0_Options::get('form_desc');
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

if(empty($client_id) || empty($domain)){ ?>

    <p><?php _e('Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', WPA0_LANG); ?></p>

<?php } else { ?>
    <div id="form-signin-wrapper" class="auth0-login">
        <?php include 'error-msg.php'; ?>
        <div class="form-signin">

            <?php if ($showAsModal) { ?>

                <button id="a0LoginButton" onclick="a0ShowLoginModal();" ><?php echo $modalTriggerName; ?></button>

            <?php } else { ?>
                <div id="auth0-login-form">
                </div>
            <?php } ?>
            <?php if ($wordpress_login_enabled && $canShowLegacyLogin) { ?>
                <div id="extra-options">
                    <a href="?wle">Login with WordPress username</a>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php if (!empty($extra_css)) { ?>
        <style type="text/css">
            <?php echo $extra_css; ?>
        </style>
    <?php } ?>
    <script id="auth0" src="<?php echo $cdn ?>"></script>
    <script type="text/javascript">
        var callback = null;
        if(typeof(a0_wp_login) === "object") {
            callback = a0_wp_login.initialize
        }

        var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');

    <?php


        $options_obj = WP_Auth0::buildSettings(WP_Auth0_Options::get_options());

        $options_obj = array_merge( array(
            "callbackURL"   =>  site_url('/index.php?auth0=1'),
            "authParams"    => array("state" => $state),
        ), $options_obj  );

        if (isset($specialSettings)){
            $options_obj = array_merge( $options_obj , $specialSettings );
        }

        if (!$showAsModal){
            $options_obj['container'] = 'auth0-login-form';
        }


        $options = json_encode($options_obj);
    ?>
        function a0ShowLoginModal() {
            var options = <?php echo $options; ?>;

        <?php if ($allow_signup) { ?>
            lock.show(options, callback);
        <?php } else { ?>
            lock.showSignin(options, callback);
        <?php } ?>
        }

    <?php if (!$showAsModal) { ?>
        a0ShowLoginModal();
    <?php } ?>

    </script>
<?php
}
?>