<?php

class WP_Auth0_SocialAmplification_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            $this->getWidgetId(),
            __($this->getWidgetName(), 'wp_auth0_widget_domain'),
            array( 'description' => __( $this->getWidgetDescription(), 'wpb_widget_domain' ) )
        );
    }

    protected function getWidgetId()
    {
        return 'wp_auth0_social_amplification_widget';
    }

    protected function getWidgetName()
    {
        return "Auth0 Social Amplification";
    }

    protected function getWidgetDescription()
    {
        return "Shows Auth0 Social Amplification Widget Embed in your sidebar";
    }

    public function form( $instance ) {
        require WPA0_PLUGIN_DIR . 'templates/a0-widget-amplificator.php';
    }

    public function update( $new_instance, $old_instance ) {
        $options = WP_Auth0_Options::Instance();
        $options->set('social_facebook_message', $new_instance['social_facebook_message']);
        $options->set('social_twitter_message', $new_instance['social_twitter_message']);
        return $new_instance;
    }

    public function widget( $args, $instance ) {

        $client_id = WP_Auth0_Options::Instance()->get('client_id');
        $userData = WP_Auth0_DBManager::get_current_user_profiles();

        if (trim($client_id) != "" && $userData)
        {
            $options = WP_Auth0_Options::Instance();

            $supportedProviders = array();

            if (!empty($options->get('social_facebook_key'))) {
                $supportedProviders[] = 'facebook';
            }

            if (!empty($options->get('social_twitter_key'))) {
                $supportedProviders[] = 'twitter';
            }

            $providers = array();
            foreach ($userData as $value) {
                foreach ($value->identities as $identity) {
                    $providers[] = $identity->provider;
                }
            }

            $providers = array_intersect(array_unique($providers), $supportedProviders);

            if ( count($providers) > 0 ) {
                echo $args['before_widget'];

                wp_enqueue_style('auth0-aplificator-css', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/css/amplificator.css');

                wp_register_script('auth0-aplificator-js', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/js/amplificator.js');
                wp_localize_script('auth0-aplificator-js', 'auth0_ajax',array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
                wp_enqueue_script('auth0-aplificator-js');

                foreach ($providers as $provider) {
                ?>
                    <button onclick="Auth0Amplify('<?php echo $provider; ?>')"><?php echo $provider; ?></button>
                <?php
                }

                echo $args['after_widget'];
            }
        }

    }

}
