<?php
class WP_Auth0_Users {
	public static function create_user( $userinfo ){
		$email = $userinfo->email;
		if (empty($email)) {
			$email = "change_this_email@" . uniqid() .".com";
		}

		$valid_user = apply_filters( 'wpa0_should_create_user', true, $userinfo );
		if(!$valid_user)
			return -2;

		// Generate a random password
		$password = wp_generate_password();

		// Split the name into first- and lastname
		$names = explode(" ", $userinfo->name);

		$firstname = "";
		$lastname = "";
		if(count($names) == 1)
			$firstname = $userinfo->name;
		elseif(count($names) == 2){
			$firstname = $names[0];
			$lastname = $names[1];
		}else{
			$lastname = array_pop($names);
			$firstname = implode(" ", $names);
		}

		$username = $userinfo->nickname;
		if (empty($username)) {
			$username = $email;
		}
		// Create the user data array for updating first- and lastname
		$user_data = array(
			'user_email' => $email,
			'user_login' => $username,
			'user_pass' => $password,
			'first_name' => $firstname,
			'last_name' => $lastname,
			'display_name' => $username
		);

		// Update the user
		$user_id = wp_insert_user( $user_data );

		if(!is_numeric($user_id))
			return -1;

		do_action( 'wpa0_user_created', $user_id, $email, $password, $firstname, $lastname );

		// Return the user ID
		return $user_id;
	}
}
