<?php
/**
 * Contains Class TestUserRepoMeta.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestUserRepoMeta.
 * Tests that user meta is added, retrieved, and deleted properly.
 */
class TestUserRepoMeta extends WP_Auth0_Test_Case {

	use UsersHelper;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
	}

	/**
	 * Test that a user is found by their auth0_id.
	 *
	 * @return void
	 */
	public function testFindAuth0UserWorksProperly() {

		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$user       = $this->createUser();
		$user_id    = $user->ID;

		$this->assertEmpty( $users_repo->find_auth0_user( $user_id ) );

		$userinfo = $this->getUserinfo();
		$users_repo->update_auth0_object( $user_id, $userinfo );

		$this->assertEquals( $user_id, $users_repo->find_auth0_user( $userinfo->sub )->ID );
	}

	/**
	 * Test that a user is found by their auth0_id.
	 *
	 * @return void
	 */
	public function testFindAuth0UserFilterWorksProperly() {

		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$user       = $this->createUser();
		$user_id    = $user->ID;

		$this->assertEmpty( $users_repo->find_auth0_user( $user_id ) );

		$userinfo = $this->getUserinfo();
		$users_repo->update_auth0_object( $user_id, $userinfo );

		$this->assertEquals( $user_id, $users_repo->find_auth0_user( $userinfo->sub )->ID );

		$user_2 = get_user_by( 'id', $this->createUser()->ID );
		add_filter(
			'find_auth0_user',
			function( $value, $id ) use ( $user_2 ) {
				return $user_2;
			},
			100,
			3
		);
		$this->assertEquals( $user_2->ID, $users_repo->find_auth0_user( $userinfo->sub )->ID );
		remove_all_filters( 'find_auth0_user', 100 );
	}

	/**
	 * Update and get user meta.
	 */
	public function testThatUpdateMetaIsReturnedProperly() {
		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'last_update' ) );

		$userinfo = $this->getUserinfo();
		$users_repo->update_auth0_object( 1, $userinfo );

		$this->assertEquals( $userinfo->sub, $users_repo::get_meta( 1, 'auth0_id' ) );

		$saved_update = $users_repo::get_meta( 1, 'last_update' );
		$saved_update = explode( 'T', $saved_update );

		$this->assertCount( 2, $saved_update );
		$this->assertEquals( explode( 'T', date( 'c' ) )[0], $saved_update[0] );

		// Make sure all the various ways we can get the user profile come back correctly.
		$saved_userinfo = $users_repo::get_meta( 1, 'auth0_obj' );
		$this->assertEquals( WP_Auth0_Serializer::serialize( $userinfo ), $saved_userinfo );

		$saved_userinfo = WP_Auth0_Serializer::unserialize( $saved_userinfo );
		$this->assertEquals( $userinfo, $saved_userinfo );

		$saved_userinfo = get_auth0userinfo( 1 );
		$this->assertEquals( $userinfo, $saved_userinfo );

		$this->setGlobalUser( 1 );

		$saved_userinfo = get_currentauth0user();
		$this->assertEquals( $userinfo, $saved_userinfo->auth0_obj );
		$this->assertEquals( $userinfo->sub, $saved_userinfo->auth0_id );
	}

	/**
	 * Test that unique data cases are handled.
	 */
	public function testThatSpecialCharactersAreStoredProperly() {
		$userinfo   = $this->getUserinfo();
		$users_repo = new WP_Auth0_UsersRepo( self::$opts );

		// Specially-encoded characters: ¥ £ € ¢ ₡ ₢ ₣ ₤ ₥ ₦ ₪ ₯.
		$userinfo->encodedValue1 = '\u00a5 \u00a3 \u20ac \u00a2 \u20a1 \u20a2 \u20a3 \u20a4 \u20a5 \u20a6 \u20aa \u20af';

		// MySQL-escaped characters.
		$userinfo->encodedValue2 = '\\0 \\\' \\" \\b \\n \\r \\t \\Z \\ \\%  \\_';

		// Special characters.
		$userinfo->encodedValue3 = 'ⓝẸ𝐕ｅя 𝐂𝓞мⓟ𝕣σｍＩs𝔢 ό𝐍 ιĐᵉ𝓷т𝐢𝓣Ƴ 🔥🎉❓☝️✗→←';

		// "Never Compromise on Identity" in Chinese.
		$userinfo->encodedValue4 = '绝不妥协于身份';

		$users_repo->update_auth0_object( 1, $userinfo );

		$saved_userinfo = $users_repo::get_meta( 1, 'auth0_obj' );
		$saved_userinfo = WP_Auth0_Serializer::unserialize( $saved_userinfo );
		$this->assertEquals( $userinfo, $saved_userinfo );

		$saved_userinfo = get_auth0userinfo( 1 );
		$this->assertEquals( $userinfo, $saved_userinfo );
	}

	/**
	 * Make sure meta values are deleted properly.
	 */
	public function testThatDeleteMetaDeletesData() {
		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$this->storeAuth0Data( 1 );
		$users_repo::update_meta( 1, 'auth0_transient_email_update', uniqid() );

		$this->assertNotEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertNotEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertNotEmpty( $users_repo::get_meta( 1, 'last_update' ) );
		$this->assertNotEmpty( $users_repo::get_meta( 1, 'auth0_transient_email_update' ) );

		wp_auth0_delete_auth0_object( 1 );

		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'last_update' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_transient_email_update' ) );
	}

		/**
		 * Test get user meta filters.
		 */
	public function testGetMetaFilterWorksProperly() {
		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );

		$userinfo = $this->getUserinfo();
		$users_repo->update_auth0_object( 1, $userinfo );

		$this->assertEquals( $userinfo->sub, $users_repo::get_meta( 1, 'auth0_id' ) );

		$saved_userinfo = $users_repo::get_meta( 1, 'auth0_obj' );
		$this->assertEquals( WP_Auth0_Serializer::serialize( $userinfo ), $saved_userinfo );

		add_filter(
			'auth0_get_meta',
			function( $value, $user_id, $key ) {
				if ( $key === 'auth0_id' ) {
					return '82';
				}
				return $value;
			},
			100,
			3
		);

		// The auth0_id meta should be filtered.
		$this->assertEquals( '82', $users_repo::get_meta( 1, 'auth0_id' ) );
		// The auth0_obj should not.
		$this->assertEquals( WP_Auth0_Serializer::serialize( $userinfo ), $users_repo::get_meta( 1, 'auth0_obj' ) );

		remove_all_filters( 'auth0_get_meta', 100 );
	}

	/**
	 * Test update user meta filters.
	 */
	public function testUpdateMetaFilterWorksProperly() {
		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );

		$userinfo = $this->getUserinfo();

		add_filter(
			'auth0_update_meta',
			function( $value, $user_id, $key ) {
				global $wpdb;
				if ( $key === 'auth0_id' ) {
					update_user_meta( $user_id, $wpdb->prefix . $key, '82' );
					return true;
				}
				return $value;
			},
			100,
			3
		);

		$users_repo->update_auth0_object( 1, $userinfo );
		$this->assertEquals( '82', $users_repo::get_meta( 1, 'auth0_id' ) );

		remove_all_filters( 'auth0_update_meta', 100 );

		$users_repo->update_auth0_object( 1, $userinfo );
		$this->assertEquals( $userinfo->sub, $users_repo::get_meta( 1, 'auth0_id' ) );

		$saved_userinfo = $users_repo::get_meta( 1, 'auth0_obj' );
		$this->assertEquals( WP_Auth0_Serializer::serialize( $userinfo ), $saved_userinfo );
	}

	/**
	 * Test delete user meta filters.
	 */
	public function testDeleteMetaFilterWorksProperly() {
		$users_repo = new WP_Auth0_UsersRepo( self::$opts );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );

		$userinfo = $this->getUserinfo();
		$users_repo->update_auth0_object( 1, $userinfo );
		$this->assertEquals( $userinfo->sub, $users_repo::get_meta( 1, 'auth0_id' ) );

		add_filter(
			'auth0_delete_meta',
			function( $value, $user_id, $key ) {
				if ( $key === 'auth0_id' ) {
					// Skipping deletion.
					return true;
				}
				return $value;
			},
			100,
			3
		);

		$users_repo::delete_meta( 1, 'auth0_id' );
		// It shouldn't be deleted.
		$this->assertEquals( $userinfo->sub, $users_repo::get_meta( 1, 'auth0_id' ) );

		remove_all_filters( 'auth0_delete_meta', 100 );

		$saved_userinfo = $users_repo::get_meta( 1, 'auth0_obj' );
		$this->assertEquals( WP_Auth0_Serializer::serialize( $userinfo ), $saved_userinfo );
	}


}
