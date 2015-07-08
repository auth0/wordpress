<?php

class WP_Auth0_Dashboard_Options  extends WP_Auth0_Options_Generic {

    protected static $instance = null;
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new WP_Auth0_Options;
        }
        return self::$instance;
    }

    protected $options_name = 'wp_auth0_dashboard_settings';

    public function is_wp_registration_enabled()
    {
        return (get_option('users_can_register', 0) == 1);
    }

    protected function defaults(){
        return array(
            'chart_idp_type' => 'pie',
            'chart_gender_type' => 'pie',
            'chart_age_type' => 'pie',
        );
    }
}
