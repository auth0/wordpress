<?php
$auth0_options = WP_Auth0_Options::Instance();
?>

<div id="form-signin-wrapper" class="auth0-login" data-auth0-opts="<?php echo esc_attr( json_encode( $specialSettings ) ) ?>">
    <?php include 'error-msg.php'; ?>
    <div class="form-signin">
        <div id="auth0-login-form"></div>
        <?php if ( ! empty( $specialSettings['show_as_modal'] ) ) : ?>
            <button id="a0LoginButton" ><?php
              echo ! empty ( $specialSettings['modal_trigger_name'] )
                ? sanitize_text_field($specialSettings['modal_trigger_name'] )
                : __( 'Login', 'wp-auth0' )
              ?></button>
        <?php endif; ?>

        <?php if ( $auth0_options->get( 'wordpress_login_enabled' ) && function_exists( 'login_header' ) ) { ?>
            <div id="extra-options">
                <a href="<?php echo wp_login_url() ?>?wle">
                  <?php _e( 'Login with WordPress username', 'wp-auth0' ) ?>
                </a>
            </div>
        <?php } ?>

    </div>
</div>

<style type="text/css">
    <?php echo apply_filters( 'auth0_login_css', '' ); ?>
    <?php echo $auth0_options->get( 'custom_css' ); ?>
</style>

<?php if ( $custom_js = $auth0_options->get( 'custom_js' ) ) : ?>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    <?php echo $custom_js; ?>
});
</script>
<?php endif; ?>