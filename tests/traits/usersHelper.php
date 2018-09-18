<?php
/**
 * Contains Trait Users.
 *
 * @package WP-Auth0
 * @since 3.8.0
 */

/**
 * Trait Users.
 */
trait UsersHelper {

	/**
	 * Create a new User.
	 *
	 * @param null $email - Email to use, default is used if none provided.
	 *
	 * @return null|object|stdClass
	 */
	public function createUser( $email = null ) {
		$username = 'test_new_user' . uniqid();
		$user     = wp_insert_user(
			[
				'user_login' => $username,
				'user_email' => $email ? $email : $username . '@example.com',
				'user_pass'  => uniqid(),
			]
		);

		return is_wp_error( $user ) ? null : get_user_by( 'id', $user )->data;
	}

	/**
	 * Create a userinfo object.
	 *
	 * @param string $strategy - Strategy to use for the sub.
	 *
	 * @return stdClass
	 */
	public function getUserinfo( $strategy = 'test-strategy' ) {
		$name            = 'test_new_user' . uniqid();
		$userinfo        = new stdClass();
		$userinfo->sub   = $strategy . '|' . uniqid();
		$userinfo->name  = $name;
		$userinfo->email = $name . '@example.com';
		return $userinfo;
	}
}
