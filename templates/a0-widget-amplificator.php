<?php

$options = WP_Auth0_Options::Instance();
$social_facebook_message = $options->get( 'social_facebook_message' );
$social_twitter_message = $options->get( 'social_twitter_message' );
$amplificator_title = $options->get( 'amplificator_title' );
$amplificator_subtitle = $options->get( 'amplificator_subtitle' );
$auth0_implicit_workflow = $options->get( 'auth0_implicit_workflow' );

?>

<?php if ( $auth0_implicit_workflow ) { ?>
<p style="color:red;">
    This widget needs access to the Facebook and Twitter APIs to work. Make sure your server has internet access (or at least access to these APIs).
</p>
<?php } ?>

<p>
    <label for="<?php echo $this->get_field_id( 'amplificator_title' ); ?>"><?php _e( 'Widget title:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( 'amplificator_title' ); ?>"
           name="<?php echo $this->get_field_name( 'amplificator_title' ); ?>" ><?php echo esc_attr( $amplificator_title ); ?></textarea>
</p>

<p>
    <label for="<?php echo $this->get_field_id( 'amplificator_subtitle' ); ?>"><?php _e( 'Widget subtitle:' ); ?></label>
    <textarea class="widefat" id="<?php echo $this->get_field_id( '$amplificator_subtitle' ); ?>"
           name="<?php echo $this->get_field_name( 'amplificator_subtitle' ); ?>" ><?php echo esc_attr( $amplificator_subtitle ); ?></textarea>
</p>

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

<p>
    You can use the following tags that will be replaced with the proper info after publish the status:
    <ul>
        <li><b>%page_url%</b>: This will be replaced by the actual page url.</li>
        <li><b>%site_url%</b>: This will be replaced by the site url.</li>
    </ul>
</p>

<p>
    <i>
        <b>Note:</b> to use the amplificator, you need to set up your own app credentials with the proper permisions.
        For <b>Twitter</b> your app needs to have <b>Read and write</b> access level.
        For <b>Facebook</b> your app needs to have <b>publish_actions</b> permission.
    </i>
</p>
