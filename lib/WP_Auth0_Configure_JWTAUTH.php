<?php

class WP_Auth0_Configure_JWTAUTH{

    public static function init(){
        add_action( 'admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue'));
    }

    public static function admin_enqueue(){

        if(!isset($_REQUEST['page']) || $_REQUEST['page'] != 'wpa0-jwt-auth')
            return;

        wp_enqueue_media();
        wp_enqueue_style( 'wpa0-jwt-auth', WPA0_PLUGIN_URL . 'assets/css/settings.css');

    }



    public static function render_settings_page(){

        if (isset($_POST['setup']) && $_POST['setup'] == 'Yes') {
            self::setupjwt();
        }

        $ready = WP_Auth0::isJWTConfigured();

        include WPA0_PLUGIN_DIR . 'templates/configure-jwt-auth.php';
    }

    protected static function setupjwt() {
        if(WP_Auth0::isJWTAuthEnabled()) {
            global $wpdb;

            JWT_AUTH_Options::set('aud', WP_Auth0_Options::get('client_id'));
            JWT_AUTH_Options::set('secret', WP_Auth0_Options::get('client_secret'));
            JWT_AUTH_Options::set('secret_base64_encoded', true);
            JWT_AUTH_Options::set('override_user_repo', 'WP_Auth0_UsersRepo');
            JWT_AUTH_Options::set('jwt_attribute', 'sub');
        }
    }

}
