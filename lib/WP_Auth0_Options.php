<?php

class WP_Auth0_Options {
    const OPTIONS_NAME = 'wp_auth0_settings';
    private static $_opt = null;

    private static function get_options(){
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

        update_option( self::OPTIONS_NAME, $options );
    }

    private static function defaults(){
        return array(
            'active' => 0,
            'auto_login' => 0,
            'auto_login_method' => '',
            'client_id' => '',
            'client_secret' => '',
            'domain' => '',
            'form_title' => '',
            'show_icon' => 0,
            'icon_url' => '',
            'ip_range_check' => 0,
            'ip_ranges' => '',
            'cdn_url' => '//cdn.auth0.com/w2/auth0-widget-5.min.js',
            'requires_verified_email' => true,
            'allow_signup' => true
        );
    }
}