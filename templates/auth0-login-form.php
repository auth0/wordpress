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

<?php if ( $custom_js = $auth0_options->get( 'custom_js' ) ) : ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() { <?php echo $custom_js ?> });
    </script>
<?php endif ?>