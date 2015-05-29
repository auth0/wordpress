<?php
$client_id = WP_Auth0_Options::get('client_id');

if (trim($client_id) == "") return;

$wordpress_login_enabled = WP_Auth0_Options::get('wordpress_login_enabled') == 1;

$auth0_implicit_workflow = WP_Auth0_Options::get('auth0_implicit_workflow') == 1;

$domain = WP_Auth0_Options::get('domain');
$cdn = WP_Auth0_Options::get('cdn_url');


$allow_signup = WP_Auth0_Options::is_wp_registration_enabled();

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
if (isset($_GET['redirect_to'])) {
    $stateObj["redirect_to"] = $_GET['redirect_to'];
}

$state = json_encode($stateObj);


$options_obj = WP_Auth0::buildSettings(WP_Auth0_Options::get_options());

$extraOptions = array(
    "authParams"    => array("state" => $state),
);

if(!$auth0_implicit_workflow) {
    $extraOptions["callbackURL"] = site_url('/index.php?auth0=1');
}
else {
    $extraOptions["authParams"]["scope"] = "openid name email nickname email_verified identities";
}

$options_obj = array_merge( $extraOptions, $options_obj  );

if (isset($specialSettings)){
    $options_obj = array_merge( $options_obj , $specialSettings );
}

if (!$showAsModal){
    $options_obj['container'] = 'auth0-login-form';
}

if (!$allow_signup) {
    $options_obj['disableSignupAction'] = true;
}

$options = json_encode($options_obj);

if(empty($client_id) || empty($domain)){ ?>

    <p><?php _e('Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', WPA0_LANG); ?></p>

<?php } else { ?>

    <?php if(isset($options_obj['custom_css'])) { ?>

        <style type="text/css">
            <?php echo $options_obj['custom_css'];?>
        </style>

    <?php } ?>


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
        <?php if ($auth0_implicit_workflow) { ?>
            
            callback = function(err,profile, token) {

                post('<?php echo site_url('/index.php?auth0=implicit'); ?>', {
                    token:token,
                    state:'<?php echo $state; ?>'
                }, 'POST');
                
            };

        <?php } ?>

        var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');

    <?php if(isset($options_obj['custom_js'])) { ?>

        <?php echo $options_obj['custom_js'];?> 

    <?php } ?>

        function a0ShowLoginModal() {
            var options = <?php echo $options; ?>;

            lock.show(options, callback);
        }

        function post(path, params, method) {
            method = method || "post"; // Set method to post by default if not specified.

            // The rest of this code assumes you are not using a library.
            // It can be made less wordy if you use one.
            var form = document.createElement("form");
            form.setAttribute("method", method);
            form.setAttribute("action", path);

            for(var key in params) {
                if(params.hasOwnProperty(key)) {
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", params[key]);

                    form.appendChild(hiddenField);
                 }
            }

            document.body.appendChild(form);
            form.submit();
        }

    <?php if (!$showAsModal) { ?>
        a0ShowLoginModal();
    <?php } ?>

    </script>
<?php
}
?>
