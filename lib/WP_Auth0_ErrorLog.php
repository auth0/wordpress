<?php
class WP_Auth0_ErrorLog {

    public static function init(){
        add_action( 'admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue'));
    }

    public static function admin_enqueue(){
        if(!isset($_REQUEST['page']) || $_REQUEST['page'] != 'wpa0-errors')
            return;

        wp_enqueue_media();
        wp_enqueue_style( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/css/settings.css');
        wp_enqueue_style('media');

    }

    public static function render_settings_page(){

        global $wpdb;
        $sql = 'SELECT *
                FROM ' . $wpdb->auth0_error_logs .'
                WHERE date > %s
                ORDER BY date DESC';

        $data = $wpdb->get_results($wpdb->prepare($sql, date('c', strtotime('1 month ago'))));

        if (is_null($data) || $data instanceof WP_Error ) {
            return null;
        }

        include WPA0_PLUGIN_DIR . 'templates/a0-error-log.php';
    }

}