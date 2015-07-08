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
        return (get_option('users_can_register', 0) == 1);
    }

    protected function defaults(){
        return array(
            'version' => 1,
            'auto_login' => 0,
            'auto_login_method' => '',
            'client_id' => '',
            'client_secret' => '',
            'domain' => '',
            'form_title' => '',
            'icon_url' => '',
            'ip_range_check' => 0,
            'ip_ranges' => '',
            'cdn_url' => '//cdn.auth0.com/js/lock-7.min.js',
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
            'gravatar' => true,
            'jwt_auth_integration' => false,
            // 'auto_provisioning' => true,
            'default_login_redirection' => home_url(),
        );
    }
}
