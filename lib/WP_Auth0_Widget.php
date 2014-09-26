<?php

class WP_Auth0_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(

            'wp_auth0_widget',

            __('Auth0 login widget', 'wp_auth0_widget_domain'),

            array( 'description' => __( 'Auth0 widget to embed the login form.', 'wpb_widget_domain' ), )
        );
    }

    public function widget( $args, $instance ) {

        $activated = absint(WP_Auth0_Options::get( 'active' ));

        if($activated)
        {
            echo $args['before_widget'];

            include WPA0_PLUGIN_DIR . 'templates/login-form.php';
            renderAuth0Form(false);

            echo $args['after_widget'];
        }

    }
}


