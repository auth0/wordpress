<?php

$lock_options = new WP_Auth0_Lock_Options( $specialSettings );

if ( ! $lock_options->can_show() ) {
?>
    <p><?php _e( 'Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', WPA0_LANG ); ?></p>
<?php
	return;
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'register' ) {
	$lock_options->set_signup_mode( true );
}

$extra_css = '';

if ( $lock_options->isPasswordlessEnable() ) {
	$extra_css = '.auth0-lock {margin-bottom: 50px;}';
}

$extra_css .= trim( apply_filters( 'auth0_login_css', '' ) );
$extra_css .= trim( $lock_options->get_custom_css() );

$custom_js = trim( $lock_options->get_custom_js() );

if ( empty( $title ) ) {
	$title = "Sign In";
}

$options = json_encode( $lock_options->get_lock_options() );

?>

<div id="form-signin-wrapper" class="auth0-login">
    <?php include 'error-msg.php'; ?>
    <div class="form-signin">

        <?php if ( $lock_options->show_as_modal() ) { ?>
            <button id="a0LoginButton" ><?php echo $lock_options->modal_button_name(); ?></button>
        <?php } else { ?>
            <div id="auth0-login-form"></div>
            <style>
            #attributionBadge {
                margin: 35px 0 15px;
                display: none;
                text-align: center;
                font-family:'Open Sans', sans-serif;
            }

            #attributionBadge a {
                text-decoration: none;
                color:#333;
                font-size: 16px;
                line-height: 30px;
            }
            #attributionBadge .logo {
                background: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjQ2MnB4IiBoZWlnaHQ9IjE2OHB4IiB2aWV3Qm94PSIwIDAgNDYyIDE2OCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDMuMC4zICg3ODkxKSAtIGh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaCAtLT4KICAgIDx0aXRsZT5VbnRpdGxlZDwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPjwvZGVmcz4KICAgIDxnIGlkPSJQYWdlLTEiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIHNrZXRjaDp0eXBlPSJNU1BhZ2UiPgogICAgICAgIDxnIGlkPSJsb2dvLWJsdWUtaG9yaXpvbnRhbCIgc2tldGNoOnR5cGU9Ik1TTGF5ZXJHcm91cCI+CiAgICAgICAgICAgIDxnIGlkPSJHcm91cCIgc2tldGNoOnR5cGU9Ik1TU2hhcGVHcm91cCI+CiAgICAgICAgICAgICAgICA8ZyBpZD0iQ2xpcHBlZCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTg4LjAwMDAwMCwgNDQuMDAwMDAwKSIgZmlsbD0iIzE2MjE0RCI+CiAgICAgICAgICAgICAgICAgICAgPHBhdGggZD0iTTI0Ni41MTcsMC4xMSBDMjM4LjQzOSwwLjExIDIzMS42MDcsMy45MTYgMjI2Ljc1OSwxMS4xMTUgQzIyMS45NCwxOC4yNzEgMjE5LjM5MywyOC4yNiAyMTkuMzkzLDQwIEMyMTkuMzkzLDUxLjc0IDIyMS45NCw2MS43MjkgMjI2Ljc1OSw2OC44ODQgQzIzMS42MDcsNzYuMDg0IDIzOC40MzksNzkuODg5IDI0Ni41MTcsNzkuODg5IEMyNTQuNTk1LDc5Ljg4OSAyNjEuNDI3LDc2LjA4NCAyNjYuMjc1LDY4Ljg4NCBDMjcxLjA5Myw2MS43MjkgMjczLjY0LDUxLjc0IDI3My42NCw0MCBDMjczLjY0LDI4LjI2IDI3MS4wOTMsMTguMjcxIDI2Ni4yNzUsMTEuMTE1IEMyNjEuNDI3LDMuOTE2IDI1NC41OTUsMC4xMSAyNDYuNTE3LDAuMTEgTDI0Ni41MTcsMC4xMSBaIE0yNDYuNTE3LDcwLjAwNSBDMjQyLjY1NSw3MC4wMDUgMjM5LjYwNCw2Ny44MiAyMzcuMTg3LDYzLjMyNCBDMjM0LjI2OCw1Ny44OTMgMjMyLjY2LDQ5LjYxIDIzMi42Niw0MCBDMjMyLjY2LDMwLjM5IDIzNC4yNjgsMjIuMTA2IDIzNy4xODcsMTYuNjc2IEMyMzkuNjA0LDEyLjE4IDI0Mi42NTUsOS45OTQgMjQ2LjUxNyw5Ljk5NCBDMjUwLjM3OCw5Ljk5NCAyNTMuNDMsMTIuMTggMjU1Ljg0NywxNi42NzYgQzI1OC43NjYsMjIuMTA2IDI2MC4zNzMsMzAuMzg5IDI2MC4zNzMsNDAgQzI2MC4zNzMsNDkuNjExIDI1OC43NjYsNTcuODk1IDI1NS44NDcsNjMuMzI0IEMyNTMuNDMsNjcuODIgMjUwLjM3OCw3MC4wMDUgMjQ2LjUxNyw3MC4wMDUgTDI0Ni41MTcsNzAuMDA1IFogTTcxLjQ1LDI5LjE3MiBMNzEuNDUsNjMuNDg0IEM3MS40NSw3Mi41MyA3OC44MSw3OS44ODkgODcuODU2LDc5Ljg4OSBDOTUuNzQ2LDc5Ljg4OSAxMDEuNzA3LDc1Ljk3NSAxMDMuOTAyLDc0LjI5MSBDMTA0LjAyNCw3NC4xOTcgMTA0LjE4NCw3NC4xNjkgMTA0LjMzMSw3NC4yMTYgQzEwNC40NzgsNzQuMjYzIDEwNC41OTIsNzQuMzc5IDEwNC42MzcsNzQuNTI3IEwxMDUuOTYxLDc4Ljg2IEwxMTUuNzM3LDc4Ljg2IEwxMTUuNzM3LDI5LjE3MiBMMTAzLjE3NSwyOS4xNzIgTDEwMy4xNzUsNjYuMzI2IEMxMDMuMTc1LDY2LjUwMSAxMDMuMDc2LDY2LjY2MiAxMDIuOTIxLDY2Ljc0MyBDMTAwLjU1OSw2Ny45NjEgOTUuODk5LDcwLjAwNiA5MS4yMzEsNzAuMDA2IEM4Ny4yNTIsNzAuMDA2IDg0LjAxMiw2Ni43NjggODQuMDEyLDYyLjc4NyBMODQuMDEyLDI5LjE3MiBMNzEuNDUsMjkuMTcyIEw3MS40NSwyOS4xNzIgWiBNMTk3LjIzNyw3OC44NTkgTDIwOS44LDc4Ljg1OSBMMjA5LjgsNDQuNTQ3IEMyMDkuOCwzNS41MDEgMjAyLjQ0LDI4LjE0MSAxOTMuMzk0LDI4LjE0MSBDMTg2LjczNSwyOC4xNDEgMTgxLjM5MywzMS4wMDQgMTc4LjgwMiwzMi43MSBDMTc4LjY1NywzMi44MDUgMTc4LjQ3MywzMi44MTMgMTc4LjMyMiwzMi43MzEgQzE3OC4xNzEsMzIuNjQ5IDE3OC4wNzUsMzIuNDkxIDE3OC4wNzUsMzIuMzE4IEwxNzguMDc1LDEuMTQxIEwxNjUuNTEzLDEuMTQxIEwxNjUuNTEzLDc4Ljg1OSBMMTc4LjA3NSw3OC44NTkgTDE3OC4wNzUsNDEuNzA0IEMxNzguMDc1LDQxLjUyOSAxNzguMTc0LDQxLjM2OCAxNzguMzMsNDEuMjg4IEMxODAuNjkxLDQwLjA2OSAxODUuMzUyLDM4LjAyNSAxOTAuMDE5LDM4LjAyNSBDMTkxLjk0NywzOC4wMjUgMTkzLjc2LDM4Ljc3NiAxOTUuMTIzLDQwLjEzOSBDMTk2LjQ4Niw0MS41MDIgMTk3LjIzNiw0My4zMTYgMTk3LjIzNiw0NS4yNDMgTDE5Ny4yMzYsNzguODU5IEwxOTcuMjM3LDc4Ljg1OSBaIE0xMjQuNzkyLDM5LjA1NSBMMTMyLjQzOCwzOS4wNTUgQzEzMi42OTcsMzkuMDU1IDEzMi45MDcsMzkuMjY1IDEzMi45MDcsMzkuNTI0IEwxMzIuOTA3LDY2Ljg1OCBDMTMyLjkwNyw3NC4wNDMgMTM4Ljc1Myw3OS44ODggMTQ1LjkzOCw3OS44ODggQzE0OC41NDMsNzkuODg4IDE1MS4xMTMsNzkuNTEyIDE1My41ODUsNzguNzcgTDE1My41ODUsNjkuNzk2IEMxNTIuMTQzLDY5LjkyMyAxNTAuNDg1LDcwLjAwNSAxNDkuMzEzLDcwLjAwNSBDMTQ3LjE5Myw3MC4wMDUgMTQ1LjQ2OSw2OC4yOCAxNDUuNDY5LDY2LjE2MSBMMTQ1LjQ2OSwzOS41MjMgQzE0NS40NjksMzkuMjY0IDE0NS42NzksMzkuMDU0IDE0NS45MzgsMzkuMDU0IEwxNTMuNTg1LDM5LjA1NCBMMTUzLjU4NSwyOS4xNzEgTDE0NS45MzgsMjkuMTcxIEMxNDUuNjc5LDI5LjE3MSAxNDUuNDY5LDI4Ljk2MSAxNDUuNDY5LDI4LjcwMiBMMTQ1LjQ2OSwxMi4yOTUgTDEzMi45MDcsMTIuMjk1IEwxMzIuOTA3LDI4LjcwMiBDMTMyLjkwNywyOC45NjEgMTMyLjY5NywyOS4xNzEgMTMyLjQzOCwyOS4xNzEgTDEyNC43OTIsMjkuMTcxIEwxMjQuNzkyLDM5LjA1NSBMMTI0Ljc5MiwzOS4wNTUgWiBNNTEuMzYxLDc4Ljg1OSBMNjQuNDI5LDc4Ljg1OSBMNDQuNTU1LDkuNTUgQzQyLjk2MiwzLjk5MiAzNy44MTEsMC4xMSAzMi4wMjksMC4xMSBDMjYuMjQ3LDAuMTEgMjEuMDk2LDMuOTkyIDE5LjUwMiw5LjU1IEwtMC4zNzIsNzguODU5IEwxMi42OTcsNzguODU5IEwxOC40NDksNTguNzk4IEMxOC41MDcsNTguNTk3IDE4LjY5MSw1OC40NTkgMTguOSw1OC40NTkgTDQ1LjE1OCw1OC40NTkgQzQ1LjM2Nyw1OC40NTkgNDUuNTUyLDU4LjU5NyA0NS42MDksNTguNzk4IEw1MS4zNjEsNzguODU5IEw1MS4zNjEsNzguODU5IFogTTQyLjA1Niw0OC41NzYgTDIyLjAwNCw0OC41NzYgQzIxLjg1Nyw0OC41NzYgMjEuNzE4LDQ4LjUwNyAyMS42MjksNDguMzg4IEMyMS41NDEsNDguMjcyIDIxLjUxMyw0OC4xMTkgMjEuNTUzLDQ3Ljk3OCBMMzEuNTc5LDEzLjAxMiBDMzEuNjM3LDEyLjgxMSAzMS44MjEsMTIuNjczIDMyLjAzLDEyLjY3MyBDMzIuMjM5LDEyLjY3MyAzMi40MjMsMTIuODExIDMyLjQ4LDEzLjAxMiBMNDIuNTA3LDQ3Ljk3OCBDNDIuNTQ3LDQ4LjEyIDQyLjUxOSw0OC4yNzIgNDIuNDMsNDguMzg4IEM0Mi4zNDIsNDguNTA3IDQyLjIwMyw0OC41NzYgNDIuMDU2LDQ4LjU3NiBMNDIuMDU2LDQ4LjU3NiBaIiBpZD0iU2hhcGUiPjwvcGF0aD4KICAgICAgICAgICAgICAgIDwvZz4KICAgICAgICAgICAgICAgIDxnIGlkPSJDbGlwcGVkIiBmaWxsPSIjRUI1NDI0Ij4KICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMTE5LjU1NSwxMzUuODYxIEwxMDIuNzA1LDgzLjk5NyBMMTQ2LjgxMyw1MS45NTIgTDkyLjI5MSw1MS45NTIgTDc1LjQ0LDAuMDkgTDc1LjQzNSwwLjA3NiBMMTI5Ljk2NSwwLjA3NiBMMTQ2LjgyLDUxLjk0NyBMMTQ2LjgyMSw1MS45NDYgTDE0Ni44MzUsNTEuOTM4IEMxNTYuNjIzLDgyLjAzIDE0Ni41NDIsMTE2LjI1NiAxMTkuNTU1LDEzNS44NjEgTDExOS41NTUsMTM1Ljg2MSBaIE0zMS4zMjEsMTM1Ljg2MSBMMzEuMzA3LDEzNS44NzEgTDc1LjQyNiwxNjcuOTI0IEwxMTkuNTU1LDEzNS44NjIgTDc1LjQ0LDEwMy44MDggTDMxLjMyMSwxMzUuODYxIEwzMS4zMjEsMTM1Ljg2MSBaIE00LjA1Miw1MS45MzkgTDQuMDUyLDUxLjkzOSBDLTYuMjUyLDgzLjY2IDUuNzA5LDExNy4yNzIgMzEuMzEyLDEzNS44NjcgTDMxLjMxNiwxMzUuODUxIEw0OC4xNjgsODMuOTkgTDQuMDcsNTEuOTUxIEw1OC41NzksNTEuOTUxIEw3NS40MzEsMC4wODkgTDc1LjQzNSwwLjA3NSBMMjAuOTAyLDAuMDc1IEw0LjA1Miw1MS45MzkgTDQuMDUyLDUxLjkzOSBaIiBpZD0iU2hhcGUiPjwvcGF0aD4KICAgICAgICAgICAgICAgIDwvZz4KICAgICAgICAgICAgPC9nPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+") center left no-repeat;
                background-size: 100%;
                display: inline-block;
                color: transparent;
                width: 80px;
                height: 30px;
                margin-left: 3px;
            }
</style>
            <div id="attributionBadge">
                <a href="https://auth0.com/?utm_source=wp-plugin&utm_campaign=powered-by-auth0&utm_medium=widget" rel="nofollow" target="_blank">
                Login powered by <span class="logo">Auth0</span>
                </a>
            </div>
        <?php } ?>

        <?php if ( $lock_options->get_wordpress_login_enabled() && $canShowLegacyLogin ) { ?>
            <div id="extra-options">
                <a href="?wle">Login with WordPress username</a>
            </div>
        <?php } ?>

    </div>
</div>

<?php if ( !empty( $extra_css ) ) { ?>
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
                post('<?php echo home_url( '/index.php?auth0=implicit' ); ?>', {
                    token:token,
                    state:<?php echo json_encode( $lock_options->get_state_obj() ); ?>
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
            var options = <?php echo json_encode( $lock_options->get_lock_options() ); ?>;

            lock.<?php echo $lock_options->get_lock_show_method(); ?>(options);
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

            post('<?php echo home_url( '/index.php?auth0=implicit' ); ?>', {
                    token:hashParams.id_token,
                    state:hashParams.state
                }, 'POST');

        }

    <?php } else { ?>

        function a0ShowLoginModal() {
            var options = <?php echo $options; ?>;

            lock.<?php echo $lock_options->get_lock_show_method(); ?>(options);
        }

    <?php } ?>

    var lock = new <?php echo $lock_options->get_lock_classname(); ?>('<?php echo $lock_options->get_client_id(); ?>', '<?php echo $lock_options->get_domain(); ?>');

    <?php if ( ! empty( $custom_js ) ) { ?>

        <?php echo $custom_js;?>

    <?php } ?>

    <?php if ( ! $lock_options->show_as_modal() ) { ?>
        a0ShowLoginModal();
    <?php } else { ?>

        jQuery('#a0LoginButton').click(a0ShowLoginModal);

    <?php } ?>

    lock.on('ready', function(){
        if ( lock.options['$client'].subscription === 'free' ) {
            jQuery('#attributionBadge').fadeIn();
        }

        jQuery(".a0-footer").parent().style('margin-bottom', '50px');
    });

    lock.on('error shown', function(){
        jQuery(".a0-footer").parent().css('margin-bottom', '50px');
    });

});
</script>
