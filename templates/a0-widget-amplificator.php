<?php

$options = WP_Auth0_Options::Instance();
$social_facebook_message = $options->get('social_facebook_message');
$social_twitter_message = $options->get('social_twitter_message');

?>

<p>
    <label for="<?php echo $this->get_field_id( 'social_facebook_message' ); ?>"><?php _e( 'Facebook message:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'social_facebook_message' ); ?>"
           name="<?php echo $this->get_field_name( 'social_facebook_message' ); ?>" ><?php echo esc_attr( $social_facebook_message ); ?></textarea>
</p>


<p>
    <label for="<?php echo $this->get_field_id( 'social_twitter_message' ); ?>"><?php _e( 'Twitter message:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'social_twitter_message' ); ?>"
           name="<?php echo $this->get_field_name( 'social_twitter_message' ); ?>" ><?php echo esc_attr( $social_twitter_message ); ?></textarea>
</p>
