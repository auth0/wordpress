<?php

class WP_Auth0_Dashboard_Options  extends WP_Auth0_Options_Generic {

    protected static $instance = null;
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new WP_Auth0_Dashboard_Options;
        }
        return self::$instance;
    }

    protected $options_name = 'wp_auth0_dashboard_settings';

    public function get_options_name() {
        return $this->options_name;
    }

    protected function defaults(){
        return array(
            'chart_idp_type' => 'donut',
            'chart_gender_type' => 'donut',
            'chart_age_type' => 'donut',

            'chart_age_from' => '10',
            'chart_age_to' => '70',
            'chart_age_step' => '5',
        );
    }
}
