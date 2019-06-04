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

		$users_repo->delete_auth0_object( 1 );

		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'last_update' ) );
		$this->assertEmpty( $users_repo::get_meta( 1, 'auth0_transient_email_update' ) );
	}
}
