<?php
$auth0_options = WP_Auth0_Options::Instance();
$wle = (bool) $auth0_options->get( 'wordpress_login_enabled', true );
?>
    <div id="form-signin-wrapper" class="auth0-login">
        <div class="form-signin">
            <div id="<?php echo esc_attr( WPA0_AUTH0_LOGIN_FORM_ID ) ?>"></div>
          <?php if ( $wle && function_exists( 'login_header' ) ) : ?>
              <div id="extra-options">
                  <a href="<?php echo wp_login_url() ?>?wle">
                    <?php _e( 'Login with WordPress username', 'wp-auth0' ) ?>
                  </a>
              </div>
          <?php endif ?>
        </div>
    </div>

    <style type="text/css">
        <?php echo apply_filters( 'auth0_login_css', '' ) ?>
        <?php echo $auth0_options->get( 'custom_css' ) ?>
    </style>

<?php
$custom_js = (string) trim( $auth0_options->get( 'custom_js' ) );
$custom_signup_fields = (string) trim( $auth0_options->get( 'custom_signup_fields' ) );

if ( $custom_js || $custom_signup_fields ) {
  echo '<script type="text/javascript">';
  if ( $custom_js ) {
    echo 'document.addEventListener("DOMContentLoaded", function() {' . $custom_js . '});';
  }
  if ( $custom_signup_fields ) {
    echo 'var ' . WP_Auth0_Lock10_Options::LOCK_GLOBAL_JS_VAR_NAME . 'Fields=' . $custom_signup_fields . ';';
  }
  echo '</script>';
}
?>