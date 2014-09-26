<?php

class WP_Auth0_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'wp_auth0_widget',
            __('Auth0 login widget', 'wp_auth0_widget_domain'),
            array( 'description' => __( 'Auth0 widget to embed the login form.', 'wpb_widget_domain' ), )
        );
    }

    public function form( $instance ) {

        wp_enqueue_media();
        wp_enqueue_script( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/js/admin.js', array('jquery'));
        wp_enqueue_style('media');
        wp_localize_script( 'wpa0_admin', 'wpa0', array(
            'media_title' => __('Choose your icon', WPA0_LANG),
            'media_button' => __('Choose icon', WPA0_LANG)
        ));
        require WPA0_PLUGIN_DIR . 'templates/a0-widget-setup-form.php';

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


