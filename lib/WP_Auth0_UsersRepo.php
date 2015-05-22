<?php

class WP_Auth0_UsersRepo {

    public static function init() {
        if (WP_Auth0_Options::get('jwt_auth_integration')) {
            add_filter( 'wp_jwt_auth_get_user', array( __CLASS__, 'getUser' ), 1);
        }
    }

    public static function getUser($jwt) { 
        global $wpdb;

        $sql = 'SELECT u.*
                FROM ' . $wpdb->auth0_user .' a
                JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
                WHERE a.auth0_id = %s;';

        $userRow = $wpdb->get_row($wpdb->prepare($sql, $jwt->sub));

        if (is_null($userRow)) {
            return null;
        }elseif($userRow instanceof WP_Error ) {
            self::insertAuth0Error('findAuth0User',$userRow);
            return null;
        }
        $user = new WP_User();
        $user->init($userRow);
        return $user;

    }

}
