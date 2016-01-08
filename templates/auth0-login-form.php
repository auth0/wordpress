<?php

$lock_options = new WP_Auth0_Lock_Options($specialSettings);

if ( ! $lock_options->can_show() ) {
?>
    <p><?php _e('Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', WPA0_LANG); ?></p>
<?php
    return;
}

$extra_css = trim(apply_filters( 'auth0_login_css', ''));
$extra_css .= trim($lock_options->get_custom_css());

$custom_js = trim($lock_options->get_custom_js());

if (empty($title)) {
    $title = "Sign In";
}

$options = json_encode($lock_options->get_lock_options());

?>

    <div id="form-signin-wrapper" class="auth0-login">
        <?php include 'error-msg.php'; ?>
        <div class="form-signin">

            <?php if ($lock_options->show_as_modal()) { ?>
                <button id="a0LoginButton" ><?php echo $lock_options->modal_button_name(); ?></button>
            <?php } else { ?>
                <div id="auth0-login-form"></div>
            <?php } ?>


            <?php if ($lock_options->get_wordpress_login_enabled() && $canShowLegacyLogin) { ?>
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

    <script type="text/javascript">
    var ignore_sso = false;
    document.addEventListener("DOMContentLoaded", function() {

        var callback = null;


        <?php if ( $lock_options->get_auth0_implicit_workflow() ) { ?>

            callback = function(err,profile, token) {

                if (!err) {
                    post('<?php echo home_url('/index.php?auth0=implicit'); ?>', {
                        token:token,
                        state:<?php echo json_encode($lock_options->get_state_obj()); ?>
                    }, 'POST');
                }

            };

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

                        var value = params[key];

                        if (typeof(value) === 'object') {
                            value = JSON.stringify(value);
                        }

                        hiddenField.setAttribute("value", value);

                        form.appendChild(hiddenField);
                     }
                }

                document.body.appendChild(form);
                form.submit();
            }

            function a0ShowLoginModal() {
                var options = <?php echo json_encode($lock_options->get_lock_options()); ?>;

                lock.show(options, callback);
            }

            function getHashParams() {

                var hashParams = {};
                var e,
                    a = /\+/g,  // Regex for replacing addition symbol with a space
                    r = /([^&;=]+)=?([^&;]*)/g,
                    d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
                    q = window.location.hash.substring(1);

                while (e = r.exec(q))
                   hashParams[d(e[1])] = d(e[2]);

                return hashParams;
            }

            var hashParams = getHashParams();
            if (hashParams && hashParams.id_token) {
                ignore_sso = true;
                callback(null,null, hashParams.id_token);

            }

        <?php } else { ?>

            function a0ShowLoginModal() {
                var options = <?php echo $options; ?>;

                lock.show(options, '<?php echo $lock_options->get_code_callback_url(); ?>');
            }

        <?php } ?>


        var lock = new Auth0Lock('<?php echo $lock_options->get_client_id(); ?>', '<?php echo $lock_options->get_domain(); ?>');

        <?php if( ! empty( $custom_js )) { ?>

            <?php echo $custom_js;?>

        <?php } ?>

        <?php if ( ! $lock_options->show_as_modal() ) { ?>
            a0ShowLoginModal();
        <?php } else { ?>

            jQuery('#a0LoginButton').click(a0ShowLoginModal);

        <?php } ?>

    });
    </script>
