<?php

class WP_Auth0_SocialAmplification_Widget extends WP_Widget {

    protected static $db_manager;
    protected static $social_amplificator;

    public static function set_context(WP_Auth0_DBManager $db_manager, WP_Auth0_Amplificator $social_amplificator) {
      self::$db_manager = $db_manager;
      self::$social_amplificator = $social_amplificator;
    }

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
        $options->set('amplificator_title', $new_instance['amplificator_title']);
        $options->set('amplificator_subtitle', $new_instance['amplificator_subtitle']);
        return $new_instance;
    }

    public function widget( $args, $instance ) {

        $client_id = WP_Auth0_Options::Instance()->get('client_id');
        $userData = self::$db_manager->get_current_user_profiles();

        if (trim($client_id) != "" && $userData)
        {
            $options = WP_Auth0_Options::Instance();

            $supportedProviders = array();

            $social_facebook_key = $options->get('social_facebook_key');
            if (!empty($social_facebook_key)) {
                $supportedProviders[] = 'facebook';
            }

            $social_twitter_key = $options->get('social_twitter_key');
            if (!empty($social_twitter_key)) {
                $supportedProviders[] = 'twitter';
            }

            $providers = array();
            foreach ($userData as $value) {
                foreach ($value->identities as $identity) {
                    $providers[] = $identity->provider;
                }
            }

            $providers = array_intersect(array_unique($providers), $supportedProviders);

            echo $args['before_widget'];

            $amplificator_title = $options->get('amplificator_title');
            $amplificator_subtitle = $options->get('amplificator_subtitle');

            if (!empty($amplificator_title)) {
                echo "<h2 class=\"widget-title\">$amplificator_title</h2>";
            }

            if (!empty($amplificator_subtitle)) {
                echo "<p>$amplificator_subtitle</p>";
            }

            wp_enqueue_style('auth0-aplificator-css', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/css/amplificator.css');

            wp_register_script('auth0-aplificator-js', trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . 'assets/js/amplificator.js');
            wp_localize_script('auth0-aplificator-js', 'auth0_ajax',array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
            wp_enqueue_script('auth0-aplificator-js');

            $current_page_url = self::current_page_url();

            foreach ($supportedProviders as $provider) {

                if (in_array($provider, $providers)) {
                    $js_function = "Auth0Amplify(this,'$provider', '$current_page_url')";
                } else {

                    $current_page_url = urlencode($current_page_url);

                    switch ($provider)  {
                        case 'facebook':
                            $js_function = "javascript: void window.open('https://www.facebook.com/sharer/sharer.php?u=$current_page_url', '', 'height=300, width=600');";
                            break;
                        case 'twitter':
                            $content = self::$social_amplificator->get_share_text('twitter', $current_page_url);
                            $js_function = "javascript: void window.open('https://twitter.com/share?url=$current_page_url&text=$content', '', 'height=300, width=600');";
                            break;
                    }

                }

                ?>

<div onclick="<?php echo $js_function; ?>"
    title="<?php echo $provider; ?>"
    class="a0-social a0-<?php echo $provider; ?>" dir="ltr">

	<span><?php echo $provider; ?></span>

</div>

                <?php
            }

            echo $args['after_widget'];
        }

    }

    protected static function current_page_url() {

        return site_url($_SERVER["REQUEST_URI"]);
    }

}
