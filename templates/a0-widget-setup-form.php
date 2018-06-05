<?php
$form_title = isset( $instance[ 'form_title' ] ) ? $instance[ 'form_title' ] : '';
$social_big_buttons = isset( $instance[ 'social_big_buttons' ] ) ? $instance[ 'social_big_buttons' ] : '';
$gravatar = isset( $instance[ 'gravatar' ] ) ? $instance[ 'gravatar' ] : '';
$icon_url = isset( $instance[ 'icon_url' ] ) ? $instance[ 'icon_url' ] : '';
$dict = isset( $instance[ 'dict' ] ) ? $instance[ 'dict' ] : '';
$extra_conf = isset( $instance[ 'extra_conf' ] ) ? $instance[ 'extra_conf' ] : '';
$custom_css = isset( $instance[ 'custom_css' ] ) ? $instance[ 'custom_css' ] : '';
$custom_js = isset( $instance[ 'custom_js' ] ) ? $instance[ 'custom_js' ] : '';
$redirect_to = isset( $instance[ 'redirect_to' ] ) ? $instance[ 'redirect_to' ] : '';

if( $this->showAsModal() ) :
	$modal_trigger_name = isset( $instance[ 'modal_trigger_name' ] ) ? $instance[ 'modal_trigger_name' ] : '';
	?>
    <p>
        <label for="<?php echo $this->get_field_id( 'modal_trigger_name' ); ?>"><?php _e( 'Button text' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'modal_trigger_name' ); ?>"
               name="<?php echo $this->get_field_name( 'modal_trigger_name' ); ?>"
               type="text" value="<?php echo esc_attr( $modal_trigger_name ); ?>" />
    </p>
<?php endif; ?>
<p>
    <label for="<?php echo $this->get_field_id( 'form_title' ); ?>"><?php _e( 'Form title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'form_title' ); ?>"
           name="<?php echo $this->get_field_name( 'form_title' ); ?>"
           type="text" value="<?php echo esc_attr( $form_title ); ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'redirect_to' ); ?>"><?php _e( 'Redirect after login:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'redirect_to' ); ?>"
           name="<?php echo $this->get_field_name( 'redirect_to' ); ?>"
           type="text" value="<?php echo esc_attr( $redirect_to ); ?>" />
</p>
<p>
    <label><?php _e( 'Show big social buttons:' ); ?></label>
    <br>
    <div class="radio-wrapper">
        <input id="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_yes"
               name="<?php echo $this->get_field_name( 'social_big_buttons' ); ?>"
               type="radio" value="1" <?php echo esc_attr( $social_big_buttons ) == 1 ? 'checked="true"' : ''; ?> />
        <label for="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_yes"><?php _e( 'Yes' ); ?></label>
        &nbsp;
        <input id="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_no"
               name="<?php echo $this->get_field_name( 'social_big_buttons' ); ?>"
               type="radio" value="0" <?php echo esc_attr( $social_big_buttons ) == 0 ? 'checked="true"' : ''; ?> />
        <label for="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_no"><?php _e( 'No' ); ?></label>
        &nbsp;
        <input id="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_inherit"
               name="<?php echo $this->get_field_name( 'social_big_buttons' ); ?>"
               type="radio" value="" <?php echo esc_attr( $social_big_buttons ) === '' ? 'checked="true"' : ''; ?> />
        <label for="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_no"><?php _e( 'Default Setting' ); ?></label>
    </div>
</p>
<p>
    <label><?php _e( 'Enable Gravatar integration:' ); ?></label>
    <br>
    <div class="radio-wrapper">
        <input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_yes"
               name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
               type="radio" value="1" <?php echo esc_attr( $gravatar ) == 1 ? 'checked="true"' : ''; ?> />
        <label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_yes"><?php _e( 'Yes' ); ?></label>
        &nbsp;
        <input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"
               name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
               type="radio" value="0" <?php echo esc_attr( $gravatar ) == 0 ? 'checked="true"' : ''; ?> />
        <label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"><?php _e( 'No' ); ?></label>
        &nbsp;
        <input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_inherit"
               name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
               type="radio" value="" <?php echo esc_attr( $gravatar ) === '' ? 'checked="true"' : ''; ?> />
        <label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"><?php _e( 'Default Setting' ); ?></label>
    </div>

</p>
<p>
    <label for="<?php echo $this->get_field_id( 'icon_url' ); ?>"><?php _e( 'Icon URL:' ); ?></label>
    <input type="text" id="<?php echo $this->get_field_id( 'icon_url' ); ?>"
           name="<?php echo $this->get_field_name( 'icon_url' ); ?>"
           value="<?php echo $icon_url; ?>"/>
    <a href="javascript:void(0);" id="wpa0_choose_icon"
       related="<?php echo $this->get_field_id( 'icon_url' ); ?>"
       class="button-secondary"><?php echo _e( 'Choose Icon' ); ?></a>
    <br/><span class="description"><?php echo _e( 'The icon should be 32x32 pixels!' ); ?></span>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'dict' ); ?>"><?php _e( 'Translation:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'dict' ); ?>"
              name="<?php echo $this->get_field_name( 'dict' ); ?>">
        <?php echo esc_attr( $dict ); ?>
    </textarea>
    <br/><span class="description">
            <?php echo __( 'This is the widget\'s dict param.', 'wp-auth0' ); ?>
        <a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#dict-stringobject"><?php echo __( 'More info', 'wp-auth0' ); ?></a>
        </span><br>
        <span class="description">
            <i><b><?php echo __( 'Note', 'wp-auth0' ); ?>:</b>
                <?php echo __( 'This can override the "Form title" setting', 'wp-auth0' ); ?>
            </i>
        </span>
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'extra_conf' ); ?>"><?php _e( 'Extra configuration:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'extra_conf' ); ?>"
              name="<?php echo $this->get_field_name( 'extra_conf' ); ?>">
        <?php echo esc_attr( $extra_conf ); ?>
    </textarea>
    <br/><span class="description">
            <?php echo __( 'This field allows you to set all the widget settings.', 'wp-auth0' ); ?>
        <a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization"><?php echo __( 'More info', 'wp-auth0' ); ?></a>
        </span><br>
        <span class="description">
            <i><b><?php echo __( 'Note', 'wp-auth0' ); ?>:</b>
                <?php echo __( 'The other settings will override this configuration', 'wp-auth0' ); ?>
            </i>
        </span>
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'custom_css' ); ?>"><?php _e( 'Customize the Login Widget CSS:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'custom_css' ); ?>"
              name="<?php echo $this->get_field_name( 'custom_css' ); ?>">
        <?php echo esc_attr( $custom_css ); ?>
    </textarea>
    <br/><span class="description">
            <?php echo __( 'This should be a valid CSS to customize the Auth0 login widget.', 'wp-auth0' ); ?>
        <a target="_blank" href="https://github.com/auth0/wp-auth0#can-i-customize-the-login-widget"><?php echo __( 'More info', 'wp-auth0' ); ?></a>
        </span>
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'custom_js' ); ?>"><?php _e( 'Customize the Login Widget JS:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'custom_js' ); ?>"
              name="<?php echo $this->get_field_name( 'custom_js' ); ?>">
        <?php echo esc_attr( $custom_js ); ?>
    </textarea>
    <br/>
    <span class="description"><?php echo __( 'This should be a valid JS to customize the Auth0 login widget to, for example, add custom buttons. ', 'wp-auth0' ); ?><a target="_blank" href="https://auth0.com/docs/hrd#3"><?php echo __( 'More info', 'wp-auth0' ); ?></a></span>
</p>
