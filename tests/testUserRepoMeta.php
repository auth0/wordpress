<?php
/**
 * Contains Class TestUserRepoMeta.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestUserRepoMeta.
 * Tests that user meta is added, retrieved, and deleted properly.
 */
class TestUserRepoMeta extends TestCase {

	use setUpTestDb;

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts       = WP_Auth0_Options::Instance();
		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
	}

	/**
	 * Update and get user meta.
	 */
	public function testThatUpdateMetaIsReturnedProperly() {
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'last_update' ) );

		$userinfo = $this->getUserinfo();
		self::$users_repo->update_auth0_object( 1, $userinfo );

		$this->assertEquals( $userinfo->sub, self::$users_repo::get_meta( 1, 'auth0_id' ) );

		$saved_update = self::$users_repo::get_meta( 1, 'last_update' );
		$saved_update = explode( 'T', $saved_update );

		$this->assertCount( 2, $saved_update );
		$this->assertEquals( explode( 'T', date( 'c' ) )[0], $saved_update[0] );

		// Make sure all the various ways we can get the user profile come back correctly.
		$saved_userinfo = self::$users_repo::get_meta( 1, 'auth0_obj' );
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
		$userinfo = $this->getUserinfo();

		// Specially-encoded characters: ¥ £ € ¢ ₡ ₢ ₣ ₤ ₥ ₦ ₪ ₯.
		$userinfo->encodedValue1 = '\u00a5 \u00a3 \u20ac \u00a2 \u20a1 \u20a2 \u20a3 \u20a4 \u20a5 \u20a6 \u20aa \u20af';

		// MySQL-escaped characters.
		$userinfo->encodedValue2 = '\\0 \\\' \\" \\b \\n \\r \\t \\Z \\ \\%  \\_';

		// Special characters.
		$userinfo->encodedValue3 = 'ⓝẸ𝐕ｅя 𝐂𝓞мⓟ𝕣σｍＩs𝔢 ό𝐍 ιĐᵉ𝓷т𝐢𝓣Ƴ 🔥🎉❓☝️✗→←';

		// "Never Compromise on Identity" in Chinese.
		$userinfo->encodedValue4 = '绝不妥协于身份';

		self::$users_repo->update_auth0_object( 1, $userinfo );

		$saved_userinfo = self::$users_repo::get_meta( 1, 'auth0_obj' );
		$saved_userinfo = WP_Auth0_Serializer::unserialize( $saved_userinfo );
		$this->assertEquals( $userinfo, $saved_userinfo );

		$saved_userinfo = get_auth0userinfo( 1 );
		$this->assertEquals( $userinfo, $saved_userinfo );
	}

	/**
	 * Make sure meta values are deleted properly.
	 */
	public function testThatDeleteMetaDeletesData() {
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'last_update' ) );

		$this->storeAuth0Data( 1 );

		$this->assertNotEmpty( self::$users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertNotEmpty( self::$users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertNotEmpty( self::$users_repo::get_meta( 1, 'last_update' ) );

		self::$users_repo->delete_auth0_object( 1 );

		$this->assertEmpty( self::$users_repo::get_meta( 1, 'auth0_id' ) );
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'auth0_obj' ) );
		$this->assertEmpty( self::$users_repo::get_meta( 1, 'last_update' ) );
	}

	/**
	 * Run after every test.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$users_repo->delete_auth0_object( 1 );
	}
}
