<?php if ( isset( $_GET['message'] ) ): ?>
    <?php $status = intval( $_GET['message'] ); ?>
    <?php if ( $status == 1 ): ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( '<strong>Email sent:</strong> In a minute or two, you should get an email with the password reset link.', LP_THEME_LANG ); ?>
        </div>
    <?php elseif ( $status == 2 ): ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( '<strong>Congratulations:</strong> Your password has been changed and you can now log in below.', LP_THEME_LANG ); ?>
        </div>
    <?php elseif ( $status == 3 ): ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( '<strong>Account created:</strong> Your password has been sent to your email address.', LP_THEME_LANG ); ?>
        </div>
    <?php elseif ( $status == 4 ): ?>
        <div class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( '<strong>Account created:</strong> Your account is awaiting activation. Once an administrator has activated your account, a password will be emailed to you.', LP_THEME_LANG ); ?>
        </div>
    <?php elseif ( $status == 5 ): ?>
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( '<strong>Not activated:</strong> Your account is awaiting activation.', LP_THEME_LANG ); ?>
        </div>
    <?php elseif ( $status == 6 ): ?>
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( '<strong>Error:</strong> Invalid e-mail or password.', LP_THEME_LANG ); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
