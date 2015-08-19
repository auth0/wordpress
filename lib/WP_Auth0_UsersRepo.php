<?php

class WP_Auth0_UsersRepo {

    protected $a0_options;

    public function __construct(WP_Auth0_Options $a0_options) {
      $this->a0_options = $a0_options;
    }

    public function init() {
        if (WP_Auth0_Options::Instance()->get('jwt_auth_integration') == 1) {
            add_filter( 'wp_jwt_auth_get_user', array( $this, 'getUser' ), 0,2);
        }
    }

    public function getUser($jwt, $encodedJWT) {

        global $wpdb;

        $sql = 'SELECT u.*
                FROM ' . $wpdb->auth0_user .' a
                JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
                WHERE a.auth0_id = %s;';

        $userRow = $wpdb->get_row($wpdb->prepare($sql, $jwt->sub));

        if (is_null($userRow)) {

            $domain = $this->a0_options->get( 'domain' );

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
            $this->insert_auth0_error('findAuth0User',$userRow);
            return null;
        }else{
            $user = new WP_User();
            $user->init($userRow);

            do_action( 'auth0_user_login' , $user->ID, $response, false, $encodedJWT, null );

            return $user;
        }


    }

}
