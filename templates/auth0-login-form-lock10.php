<?php

$lock_options = new WP_Auth0_Lock10_Options( $specialSettings );

if ( ! $lock_options->can_show() ) {
?>
    <p><?php _e( 'Auth0 Integration has not yet been set up! Please visit your Wordpress Auth0 settings and fill in the required settings.', 'wp-auth0' ); ?></p>
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

$options = $lock_options->get_lock_options();

?>

<div id="form-signin-wrapper" class="auth0-login">
    <?php include 'error-msg.php'; ?>
    <div class="form-signin">

        <?php if ( $lock_options->show_as_modal() ) { ?>
            <button id="a0LoginButton" ><?php echo $lock_options->modal_button_name(); ?></button>
        <?php } else { ?>
            <div id="auth0-login-form"></div>
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

document.cookie = '<?php
  echo WPA0_STATE_COOKIE_NAME ?>=<?php
  echo $options['auth']['params']['state'] ?>;max-age=<?php
  echo WP_Auth0_Nonce_Handler::COOKIE_EXPIRES ?>;path=/';


document.addEventListener("DOMContentLoaded", function() {

    var callback = null;

    var options = <?php echo json_encode( $options ); ?>;

    options.additionalSignUpFields = <?php echo $lock_options->get_custom_signup_fields(); ?>;
    <?php if ( $lock_options->get_auth0_implicit_workflow() ) { ?>

        if (window.location.hash !== '' && window.location.hash.indexOf('id_token') !== -1) {
          ignore_sso = true;
          var hash = window.location.hash;
          if (hash[0] === '#') {
            hash = hash.slice(1);
          }
          var data = hash.split('&').reduce(function(p,c,i) {
            var parts = c.split('=');
            p[parts[0]] = parts[1]
            return p;
          }, {});

          post('<?php echo site_url( 'index.php?auth0=implicit' ); ?>', {
            token:data.id_token,
            state:data.state
          }, 'POST');
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
    
    <?php } ?>

    if (!ignore_sso) {
      var lock = new <?php echo $lock_options->get_lock_classname(); ?>('<?php echo $lock_options->get_client_id(); ?>', '<?php echo $lock_options->get_domain(); ?>', options);

      <?php if ( ! empty( $custom_js ) ) { ?>

          <?php echo $custom_js;?>

      <?php } ?>

      function a0ShowLoginModal() {
          lock.<?php echo $lock_options->get_lock_show_method(); ?>();
      }

      <?php if ( ! $lock_options->show_as_modal() ) { ?>
          a0ShowLoginModal();
      <?php } else { ?>
          jQuery('#a0LoginButton').click(a0ShowLoginModal);
      <?php } ?>
    }

});
</script>
