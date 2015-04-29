<?php

class WP_Auth0_UsersRepo {

    public static function getUser($id) {
        global $wpdb;

        $sql = 'SELECT u.*
                FROM ' . $wpdb->auth0_user .' a
                JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
                WHERE a.auth0_id = %s;';

        $userRow = $wpdb->get_row($wpdb->prepare($sql, $id));

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
