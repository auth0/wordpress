<?php

class WP_Auth0_Options extends WP_Auth0_Options_Generic {

    protected static $instance = null;
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new WP_Auth0_Options;
        }
        return self::$instance;
    }

    protected $options_name = 'wp_auth0_settings';

    public function is_wp_registration_enabled()
    {
        return (get_site_option('users_can_register', 0) == 1);
    }

    public function get_enabled_connections() {
        return array('facebook','twitter','google-oauth2');
    }

    public function set_connection( $key, $value ){
        $options = $this->get_options();
        $options['connections'][$key] = $value;

        $this->set('connections', $options['connections']);
    }

    public function get_connection( $key, $default = null ){
        $options = $this->get_options();

        if(!isset($options['connections'][$key]))
            return apply_filters( 'wp_auth0_get_option', $default, $key );
        return apply_filters( 'wp_auth0_get_option', $options['connections'][$key], $key );
    }

    protected function defaults(){
        return array(
            'version' => 1,
            'metrics' => 1,
            'last_step' => 1,
            'auto_login' => 0,
            'auto_login_method' => '',
            'client_id' => '',
            'client_secret' => '',
            'domain' => '',
            'form_title' => '',
            'icon_url' => '',
            'ip_range_check' => 0,
            'ip_ranges' => '',
            'cdn_url' => '//cdn.auth0.com/js/lock-8.min.js',
            'requires_verified_email' => true,
            'wordpress_login_enabled' => true,
            'dict' => '',
            'social_big_buttons' => false,
            'username_style' => 'email',
            'extra_conf' => '',
            'remember_last_login' => true,
            'custom_css' => '',
            'custom_js' => '',
            'auth0_implicit_workflow' => false,
            'sso' => false,
            'singlelogout' => false,
            'gravatar' => true,
            'jwt_auth_integration' => false,
            'auth0_app_token' => null,
            'mfa' => null,
            'fullcontact' => null,
            'fullcontact_rule' => null,
            'fullcontact_apikey' => null,
            'geo_rule' => null,
            'income_rule' => null,
            'link_auth0_users' => null,
            'remember_users_session' => false,

            'migration_ws' => false,
            'migration_token' => null,
            'migration_token_id' => null,

            'amplificator_title' => '',
            'amplificator_subtitle' => '',

            'connections' => array(),

            'password_policy' => null,

            // 'auto_provisioning' => true,
            'default_login_redirection' => home_url(),
        );
    }
}
