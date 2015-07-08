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
?>
        <p><?php _e( 'To add this widget you need to use your own Facebook and Twitter apps.' ); ?></p>
<?php
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

    protected function getInfo() {
        global $current_user;
        global $wpdb;

        get_currentuserinfo();
        $userData = array();

        if ($current_user instanceof WP_User && $current_user->ID > 0 ) {
            $sql = 'SELECT auth0_obj
                    FROM ' . $wpdb->auth0_user .'
                    WHERE wp_id = %d';
            $results = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));

            if (is_null($results) || $results instanceof WP_Error ) {

                return null;
            }

            foreach ($results as $value) {
                $userData[] = unserialize($value->auth0_obj);
            }

        }

        return $userData;
    }

    public function widget( $args, $instance ) {

        $client_id = WP_Auth0_Options::Instance()->get('client_id');
        $userData = $this->getInfo();

        if (trim($client_id) != "" && $userData)
        {
            echo $args['before_widget'];


            $supportedProviders = array('facebook','twitter');
            $providers = array();
            foreach ($userData as $value) {
                foreach ($value->identities as $identity) {
                    $providers[] = $identity->provider;
                }
            }
            $providers = array_intersect($providers, $supportedProviders);

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
