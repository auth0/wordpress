
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
        self::$_opt = $options;
        update_option( self::OPTIONS_NAME, $options );
    }

    private static function defaults(){
        return array(
            'version' => 1,
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
            'cdn_url' => '//cdn.auth0.com/js/lock-6.min.js',
            'requires_verified_email' => true,
            'allow_signup' => true,
            'wordpress_login_enabled' => true,
            'dict' => '',
            'social_big_buttons' => 1,
            'username_style' => 'email',
        );
    }
}