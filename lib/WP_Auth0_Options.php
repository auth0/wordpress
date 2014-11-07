<?php

class WP_Auth0_Options {
    const OPTIONS_NAME = 'wp_auth0_settings';
    private static $_opt = null;

    public static function is_wp_registration_enabled()
    {
        return (get_option('users_can_register', 0) == 1);
    }

    public static function get_options(){
        if(empty(self::$_opt)){
            $options = get_option( self::OPTIONS_NAME, array());

            if(!is_array($options))
                $options = self::defaults();

            $options = array_merge( self::defaults(), $options );

            self::$_opt = $options;
        }
        return self::$_opt;
    }

    public static function get( $key, $default = null ){
        $options = self::get_options();

        if(!isset($options[$key]))
            return apply_filters( 'wp_auth0_get_option', $default, $key );
        return apply_filters( 'wp_auth0_get_option', $options[$key], $key );
    }

    public static function set( $key, $value ){
        $options = self::get_options();
        $options[$key] = $value;
        self::$_opt = $options;
        update_option( self::OPTIONS_NAME, $options );
    }

    private static function defaults(){
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
            'cdn_url' => '//cdn.auth0.com/js/lock-6.min.js',
            'requires_verified_email' => true,
            'wordpress_login_enabled' => true,
            'dict' => '',
            'social_big_buttons' => false,
            'username_style' => 'email',
            'extra_conf' => '',
            'remember_last_login' => true,
            'custom_css' => '',
            'gravatar' => true,
            'default_login_redirection' => home_url(),
        );
    }
}