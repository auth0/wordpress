<?php

$show_as_modal = isset($instance[ 'show_as_modal' ]) ? $instance[ 'show_as_modal' ] : '';
$modal_trigger_name = isset($instance[ 'modal_trigger_name' ]) ? $instance[ 'modal_trigger_name' ] : '';
$form_title = isset($instance[ 'form_title' ]) ? $instance[ 'form_title' ] : '';
$social_big_buttons = isset($instance[ 'social_big_buttons' ]) ? $instance[ 'social_big_buttons' ] : '';
$gravatar = isset($instance[ 'gravatar' ]) ? $instance[ 'gravatar' ] : '';
$show_icon = isset($instance[ 'show_icon' ]) ? $instance[ 'show_icon' ] : '';
$icon_url = isset($instance[ 'icon_url' ]) ? $instance[ 'icon_url' ] : '';
$dict = isset($instance[ 'dict' ]) ? $instance[ 'dict' ] : '';
$extra_conf = isset($instance[ 'extra_conf' ]) ? $instance[ 'extra_conf' ] : '';
$username_style = isset($instance[ 'username_style' ]) ? $instance[ 'username_style' ] : '';
$remember_last_login = isset($instance[ 'remember_last_login' ]) ? $instance[ 'remember_last_login' ] : '';

?>

<p>
    <label for="<?php echo $this->get_field_id( 'form_title' ); ?>"><?php _e( 'Form title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'form_title' ); ?>"
           name="<?php echo $this->get_field_name( 'form_title' ); ?>"
           type="text" value="<?php echo esc_attr( $form_title ); ?>" />
</p>
<p>
    <label><?php _e( 'Username style:' ); ?></label>
    <input id="<?php echo $this->get_field_id( 'username_style' ); ?>_email"
           name="<?php echo $this->get_field_name( 'username_style' ); ?>"
           type="radio" value="email" <?php echo (esc_attr( $username_style ) == 'email' ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'username_style' ); ?>_email"><?php _e( 'Email' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'username_style' ); ?>_username"
           name="<?php echo $this->get_field_name( 'username_style' ); ?>"
           type="radio" value="username" <?php echo (esc_attr( $username_style ) == 'username' ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'username_style' ); ?>_email"><?php _e( 'Username' ); ?></label>
</p>
<p>
    <label><?php _e( 'Show as modal:' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'show_as_modal' ); ?>_yes"
           name="<?php echo $this->get_field_name( 'show_as_modal' ); ?>"
           type="radio" value="1" <?php echo (esc_attr( $show_as_modal ) == 1 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'show_as_modal' ); ?>_yes"><?php _e( 'Yes' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'show_as_modal' ); ?>_no"
           name="<?php echo $this->get_field_name( 'show_as_modal' ); ?>"
           type="radio" value="0" <?php echo (esc_attr( $show_as_modal ) == 0 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'show_as_modal' ); ?>_no"><?php _e( 'No' ); ?></label>

</p>
<p>
    <label for="<?php echo $this->get_field_id( 'modal_trigger_name' ); ?>"><?php _e( 'Modal button name' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'modal_trigger_name' ); ?>"
           name="<?php echo $this->get_field_name( 'modal_trigger_name' ); ?>"
           type="text" value="<?php echo esc_attr( $modal_trigger_name ); ?>" />
</p>
<p>
    <label><?php _e( 'Show big social buttons:' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_yes"
           name="<?php echo $this->get_field_name( 'social_big_buttons' ); ?>"
           type="radio" value="1" <?php echo (esc_attr( $social_big_buttons ) == 1 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_yes"><?php _e( 'Yes' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_no"
           name="<?php echo $this->get_field_name( 'social_big_buttons' ); ?>"
           type="radio" value="0" <?php echo (esc_attr( $social_big_buttons ) == 0 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_no"><?php _e( 'No' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_inherit"
           name="<?php echo $this->get_field_name( 'social_big_buttons' ); ?>"
           type="radio" value="" <?php echo (esc_attr( $social_big_buttons ) === '' ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'social_big_buttons' ); ?>_no"><?php _e( 'Inherit' ); ?></label>

</p>
<p>
    <label><?php _e( 'Enable Gravatar integration:' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_yes"
           name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
           type="radio" value="1" <?php echo (esc_attr( $gravatar ) == 1 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_yes"><?php _e( 'Yes' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"
           name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
           type="radio" value="0" <?php echo (esc_attr( $gravatar ) == 0 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"><?php _e( 'No' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_inherit"
           name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
           type="radio" value="" <?php echo (esc_attr( $gravatar ) === '' ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"><?php _e( 'Inherit' ); ?></label>


</p>
<p>

    <label><?php _e( 'Remember last login:' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'remember_last_login' ); ?>_yes"
           name="<?php echo $this->get_field_name( 'remember_last_login' ); ?>"
           type="radio" value="1" <?php echo (esc_attr( $remember_last_login ) == 1 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'remember_last_login' ); ?>_yes"><?php _e( 'Yes' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'remember_last_login' ); ?>_no"
           name="<?php echo $this->get_field_name( 'remember_last_login' ); ?>"
           type="radio" value="0" <?php echo (esc_attr( $remember_last_login ) == 0 ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'remember_last_login' ); ?>_no"><?php _e( 'No' ); ?></label>

    <input id="<?php echo $this->get_field_id( 'remember_last_login' ); ?>_inherit"
           name="<?php echo $this->get_field_name( 'remember_last_login' ); ?>"
           type="radio" value="" <?php echo (esc_attr( $remember_last_login ) === '' ? 'checked="true"' : ''); ?> />
    <label for="<?php echo $this->get_field_id( 'remember_last_login' ); ?>_no"><?php _e( 'Inherit' ); ?></label>

</p>
<p>
    <label for="<?php echo $this->get_field_id( 'icon_url' ); ?>"><?php _e( 'Icon Url:' ); ?></label>
    <input type="text" id="<?php echo $this->get_field_id( 'icon_url' ); ?>"
           name="<?php echo $this->get_field_name( 'icon_url' ); ?>"
           value="<?php echo $icon_url; ?>"/>
    <a href="javascript:void(0);" id="wpa0_choose_icon"
       related="<?php echo $this->get_field_id( 'icon_url' ); ?>"
       class="button-secondary"><?php echo _e( 'Choose Icon' ); ?></a>
    <br/><span class="description"><?php echo _e('The icon should be 32x32 pixels!'); ?></span>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'dict' ); ?>"><?php _e( 'Translation:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'dict' ); ?>"
              name="<?php echo $this->get_field_name( 'dict' ); ?>">
        <?php echo esc_attr( $dict ); ?>
    </textarea>
    <br/><span class="description">
            <?php echo __('This is the widget\'s dict param.', WPA0_LANG); ?>
        <a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization#dict-stringobject"><?php echo __('More info', WPA0_LANG); ?></a>
        </span><br>
        <span class="description">
            <i><b><?php echo __('Note', WPA0_LANG); ?>:</b>
                <?php echo __('This will override the "Form title" setting', WPA0_LANG); ?>
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
            <?php echo __('This field allows you to set all the widget settings.', WPA0_LANG); ?>
        <a target="_blank" href="https://github.com/auth0/lock/wiki/Auth0Lock-customization"><?php echo __('More info', WPA0_LANG); ?></a>
        </span><br>
        <span class="description">
            <i><b><?php echo __('Note', WPA0_LANG); ?>:</b>
                <?php echo __('The other settings will override this configuration', WPA0_LANG); ?>
            </i>
        </span>
    </span>
</p>





