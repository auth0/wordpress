<?php
class WP_Auth0_Users {
	public static function create_user( $userinfo, $role = null ){
		$email = null;
        if (isset($userinfo->email))
        {
            $email = $userinfo->email;
        }
		if (empty($email)) {
			$email = "change_this_email@" . uniqid() .".com";
		}

		$valid_user = apply_filters( 'wpa0_should_create_user', true, $userinfo );
		if(!$valid_user)
			return -2;

		// Generate a random password
		$password = wp_generate_password();

		$firstname = "";
		$lastname = "";

		if (isset($userinfo->name)) {
			// Split the name into first- and lastname
			$names = explode(" ", $userinfo->name);

			if(count($names) == 1)
				$firstname = $userinfo->name;
			elseif(count($names) == 2){
				$firstname = $names[0];
				$lastname = $names[1];
			}else{
				$lastname = array_pop($names);
				$firstname = implode(" ", $names);
			}
		}
		
		$username = "";
		if (isset($userinfo->nickname)) {
			$username = $userinfo->nickname;
		}
		if (empty($username)) {
			$username = $email;
		}
		while(username_exists($username)) {
			$username = $username . rand(0,9);
		}
		// Create the user data array for updating first- and lastname
		$user_data = array(
			'user_email' => $email,
			'user_login' => $username,
			'user_pass' => $password,
			'first_name' => $firstname,
			'last_name' => $lastname,
			'display_name' => $username,
		);

		if ($role) {
			$user_data['role'] = 'administrator';
		}

		// Update the user
		$user_id = wp_insert_user( $user_data );

		if(!is_numeric($user_id))
			return $user_id;

		do_action( 'wpa0_user_created', $user_id, $email, $password, $firstname, $lastname );

		// Return the user ID
		return $user_id;
	}

	public static function update_auth0_object($userinfo) {
		global $wpdb;

		$wpdb->update(
			$wpdb->auth0_user,
			array(
				'auth0_obj' => WP_Auth0_Serializer::serialize($userinfo),
				'last_update' =>  date( 'c' ),
			),
			array( 'auth0_id' => $userinfo->user_id ),
			array( '%s' ),
			array( '%s' )
		);
	}

	public static function find_auth0_user( $id ) {
		global $wpdb;
		$sql = 'SELECT u.*
				FROM ' . $wpdb->auth0_user .' a
				JOIN ' . $wpdb->users . ' u ON a.wp_id = u.id
				WHERE a.auth0_id = %s';
		$userRow = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

		if ( is_null( $userRow ) ) {
			return null;
		} elseif ( $userRow instanceof WP_Error ) {
			WP_Auth0_ErrorManager::insert_auth0_error( '_find_auth0_user',$userRow );
			return null;
		}
		$user = new WP_User();
		$user->init( $userRow );
		return $user;
	}
}
