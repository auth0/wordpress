<?php

class WP_Auth0_Routes {

    public static function init() {
        add_action('parse_request', array(__CLASS__, 'custom_requests'));
    }

    public static function custom_requests ( $wp ) {
        if( ! empty($wp->query_vars['a0_action']) ) {
            switch ($wp->query_vars['a0_action']) {
                case 'oauth2-config': self::oauth2_config(); exit;
            }
        }
    }

    protected static function oauth2_config() {

        $callback_url = admin_url( 'admin.php?page=wpa0-setup&step=2' );

        echo json_encode(array(
            'redirect_uris' => array(
                $callback_url
            )
        ));
        exit;
    }
}
