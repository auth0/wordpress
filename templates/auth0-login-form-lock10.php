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
$extra_css .= trim( apply_filters( 'auth0_login_css', '' ) );
$extra_css .= trim( $lock_options->get_custom_css() );

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

    var options = {};
    options = <?php echo json_encode( $options ); ?>;
    options.additionalSignUpFields = <?php echo $lock_options->get_custom_signup_fields(); ?>;

    if (!ignore_sso) {
      var lock = new Auth0Lock(
          '<?php echo $lock_options->get_client_id(); ?>',
          '<?php echo $lock_options->get_domain(); ?>',
          options
      );

      <?php echo $lock_options->get_custom_js(); ?>

      <?php if ( ! $lock_options->show_as_modal() ) { ?>
        lock.show();
      <?php } else { ?>
          jQuery('#a0LoginButton').click(lock.show);
      <?php } ?>
    }

});
</script>
