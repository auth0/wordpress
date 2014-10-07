<?php

class WP_Auth0_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'wp_auth0_widget',
            __('Auth0 Login widget', 'wp_auth0_widget_domain'),
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

        $client_id = WP_Auth0_Options::get('client_id');

        if (trim($client_id) != "")
        {
            echo $args['before_widget'];

            $settings = WP_Auth0::buildSettings($instance);
            $settings[ 'show_as_modal' ] = isset($instance[ 'show_as_modal' ]) ? $instance[ 'show_as_modal' ] : false;
            $settings[ 'modal_trigger_name' ] = isset($instance[ 'modal_trigger_name' ]) ? $instance[ 'modal_trigger_name' ] : 'Login';

            require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
            renderAuth0Form(false, $settings);

            echo $args['after_widget'];
        }

    }

    public function update( $new_instance, $old_instance ) {
        if (trim($new_instance["dict"]) != '')
        {
            if (strpos($new_instance["dict"], '{') !== false && json_decode($new_instance["dict"]) === null)
            {
                $new_instance["dict"] = $old_instance["dict"];
            }
        }
        if (trim($new_instance["extra_conf"]) != '')
        {
            if (json_decode($new_instance["extra_conf"]) === null)
            {
                $new_instance["extra_conf"] = $old_instance["extra_conf"];
            }
        }
        return $new_instance;
    }
}


