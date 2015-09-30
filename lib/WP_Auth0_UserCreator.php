<?php

class WP_Auth0_UserCreator {

    protected $a0_options;
    protected $db_manager;

    public function __construct(WP_Auth0_Options $a0_options) {
        $this->a0_options = $a0_options;
        $this->db_manager = new WP_Auth0_DBManager();
    }

    public function tokenHasRequiredScopes($jwt) {

        return (
            (isset($jwt->email) || isset($jwt->nickname))
            && isset($jwt->identities)
            );

    }

    public function create($userinfo, $token, $access_token, $role = null, $ignore_unverified_email = false) {

// If the user doesn't exist we need to either create a new one, or asign him to an existing one
        $isDatabaseUser = false;
        foreach ($userinfo->identities as $identity) {
            if ($identity->provider == "auth0") {
                $isDatabaseUser = true;
            }
        }
        $joinUser = null;
// If the user has a verified email or is a database user try to see if there is
// a user to join with. The isDatabase is because we don't want to allow database
// user creation if there is an existing one with no verified email

        if (isset($userinfo->email) && (($ignore_unverified_email || (isset($userinfo->email_verified) && $userinfo->email_verified)) || $isDatabaseUser)) { //TODO: check this
            $joinUser = get_user_by( 'email', $userinfo->email );
        }

// $auto_provisioning = WP_Auth0_Options::get('auto_provisioning');
// $allow_signup = WP_Auth0_Options::Instance()->is_wp_registration_enabled() || $auto_provisioning;
        $allow_signup = WP_Auth0_Options::Instance()->is_wp_registration_enabled();
        $user_id = null;

        if (!is_null($joinUser) && $joinUser instanceof WP_User) {
// If we are here, we have a potential join user
// Don't allow creation or assignation of user if the email is not verified, that would
// be hijacking

            if ($ignore_unverified_email || $userinfo->email_verified) {
                $user_id = $joinUser->ID;

                $link_auth0_users = $this->a0_options->get('link_auth0_users');

                if ($access_token && $link_auth0_users && $userinfo->email_verified) {

                    $domain = $this->a0_options->get('domain');
                    $a0_main_users = $this->db_manager->get_auth0_users(array((string)$user_id));

                    if ( ! empty($a0_main_users) ) {

                        $a0_main_user_row = $a0_main_users[0];
                        $a0_main_user = unserialize( $a0_main_user_row->auth0_obj );

                        $connection_id = $this->look_for_connection_id($domain, $access_token, $userinfo->identities[0]->connection, $userinfo->identities[0]->provider);

                        $link_response = WP_Auth0_Api_Client::link_users($domain, $access_token, $a0_main_user->user_id, $userinfo->identities[0]->user_id, $userinfo->identities[0]->provider, $connection_id);

                    }
                }
            } else {
                var_dump($ignore_unverified_email || $userinfo->email_verified);exit;
                throw new WP_Auth0_EmailNotVerifiedException($userinfo, $token);
            }
        }
        if ($allow_signup && is_null($user_id)) {

// If we are here, we need to create the user
            $user_id = WP_Auth0_Users::create_user($userinfo, $role);

// Check if user was created

            if( is_wp_error($user_id) ) {
                throw new WP_Auth0_CouldNotCreateUserException($user_id->get_error_message());
            }elseif($user_id == -2){
                throw new WP_Auth0_CouldNotCreateUserException('Could not create user. The registration process were rejected. Please verify that your account is whitelisted for this system.');
            }elseif ($user_id <0){
                throw new WP_Auth0_CouldNotCreateUserException();
            }
        } elseif ( ! $allow_signup) {
            throw new WP_Auth0_RegistrationNotEnabledException();
        }

// If we are here we should have a valid $user_id with a new user or an existing one
// log him in, and update the auth0_user table
        self::insertAuth0User($userinfo, $user_id, $token, $access_token);

        return $user_id;

    }

    public function insertAuth0User($userinfo, $user_id, $id_token, $access_token) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->auth0_user,
            array(
                'auth0_id' => $userinfo->user_id,
                'wp_id' => $user_id,
                'auth0_obj' => serialize($userinfo),
                'id_token' => $id_token,
                'access_token' => $access_token,
                'last_update' =>  date( 'c' ),
                ),
            array(
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                )
            );
    }

    protected function look_for_connection_id($domain, $access_token, $connection_name, $provider) {

        $connections = WP_Auth0_Api_Client::search_connection($domain, $access_token, $provider);
        foreach ($connections as $connection) {
            if ($connection->name === $connection_name) {
                return $connection->id;
            }
        }
        return null;

    }

}
