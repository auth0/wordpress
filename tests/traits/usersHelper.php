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
	 * WP_Auth0_UsersRepo instance.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	protected static $users_repo;

	/**
	 * Create a new User.
	 *
	 * @param null        $email              - Email to use, default is used if none provided.
	 * @param null|string $password           - Password to use, default is used if empty.
	 * @param bool        $should_return_data - True to return data only, false to return WP_User.
	 *
	 * @return null|object|stdClass|WP_User
	 */
	public function createUser( $email = null, $password = null, $should_return_data = true ) {
		$username = 'test_new_user' . uniqid();
		$user_id  = wp_insert_user(
			[
				'user_login' => $username,
				'user_email' => $email ? $email : $username . '@example.com',
				'user_pass'  => $password ? $password : uniqid(),
			]
		);

		if ( is_wp_error( $user_id ) ) {
			return null;
		}

		$user = get_user_by( 'id', $user_id );
		return $should_return_data ? $user->data : $user;
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

	/**
	 * Set the global WP user.
	 *
	 * @param int $set_uid - WP user ID to set.
	 *
	 * @return int
	 */
	public function setGlobalUser( $set_uid = 1 ) {
		$GLOBALS['user_id'] = $set_uid;
		wp_set_current_user( $set_uid );
		return $set_uid;
	}

	/**
	 * Store dummy Auth0 data.
	 *
	 * @param int    $user_id - WP user ID to set.
	 * @param string $strategy - Auth0 user strategy to use.
	 */
	public function storeAuth0Data( $user_id, $strategy = 'auth0' ) {
		self::$users_repo->update_auth0_object( $user_id, $this->getUserinfo( $strategy ) );
	}
}
