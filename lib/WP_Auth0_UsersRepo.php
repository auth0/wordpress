<?php

class WP_Auth0_UsersRepo {

    public static function init() {
        if (WP_Auth0_Options::get('jwt_auth_integration') == 1) {
            add_filter( 'wp_jwt_auth_get_user', array( __CLASS__, 'getUser' ), 0,2);
        }
    }

    public static function getUser($jwt, $encodedJWT) { 

        global $wpdb;

        $sql = 'SELECT u.*
                FROM ' . $wpdb->auth0_user .' a
                JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
                WHERE a.auth0_id = %s;';

        $userRow = $wpdb->get_row($wpdb->prepare($sql, $jwt->sub));

        if (is_null($userRow)) {

            $domain = WP_Auth0_Options::get( 'domain' );

            $response = WP_Auth0_Api_Client::get_user($domain, $encodedJWT, $jwt->sub);
            
            if ($response['response']['code'] != 200) return null;

            $creator = new WP_Auth0_UserCreator();

            if ($creator->tokenHasRequiredScopes($jwt)) {
                $auth0User = $jwt;
            }
            else {
                $auth0User = json_decode($response['body']);
            }

            try {
                $user_id = $creator->create($auth0User,$encodedJWT);

                do_action( 'auth0_user_login' , $user_id, $response, true, $encodedJWT, null ); 

                return new WP_User($user_id);
            }
            catch (WP_Auth0_CouldNotCreateUserException $e) {
                return null;
            }
            catch (WP_Auth0_RegistrationNotEnabledException $e) {
                return null;
            }

            return null;
        }elseif($userRow instanceof WP_Error ) {
            self::insert_auth0_error('findAuth0User',$userRow);
            return null;
        }else{
            $user = new WP_User();
            $user->init($userRow);

            do_action( 'auth0_user_login' , $user->ID, $response, false, $encodedJWT, null ); 

            return $user;
        }
        

    }

}
