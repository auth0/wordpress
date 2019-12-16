<?php
/**
 * Contains Class TestUserMeta.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestUserMeta.
 * Tests that user meta is saved, retrieved, and deleted properly.
 */
class TestUserMeta extends WP_Auth0_Test_Case {

	use UsersHelper;

	/**
	 * Test that Auth0 meta data is created and updated properly.
	 */
	public function testUpdateAuth0Meta() {
		global $wpdb;

		$user      = $this->createUser();
		$uid       = $user->ID;
		$user_repo = new WP_Auth0_UsersRepo( self::$opts );

		$userinfo = $this->getUserinfo();
		$user_repo->update_auth0_object( $uid, $userinfo );

		$this->assertNotEmpty( get_user_meta( $uid, $wpdb->prefix . 'last_update', true ) );
		$this->assertEquals( $userinfo->sub, get_user_meta( $uid, $wpdb->prefix . 'auth0_id', true ) );

		// JSON-encoded object for complete Auth0 profile.
		$saved_userinfo = get_user_meta( $uid, $wpdb->prefix . 'auth0_obj', true );
		$saved_userinfo = json_decode( $saved_userinfo );

		$this->assertInstanceOf( 'stdClass', $saved_userinfo );
		$this->assertEquals( $userinfo->sub, $saved_userinfo->sub );
		$this->assertEquals( $userinfo->name, $saved_userinfo->name );
		$this->assertEquals( $userinfo->email, $saved_userinfo->email );

		// Run through the process again to make sure we update with new data.
		$userinfo_2 = $this->getUserinfo();
		$user_repo->update_auth0_object( $uid, $userinfo_2 );

		$this->assertEquals( $userinfo_2->sub, get_user_meta( $uid, $wpdb->prefix . 'auth0_id', true ) );

		$saved_userinfo_2 = get_user_meta( $uid, $wpdb->prefix . 'auth0_obj', true );
		$saved_userinfo_2 = json_decode( $saved_userinfo_2 );

		$this->assertInstanceOf( 'stdClass', $saved_userinfo_2 );
		$this->assertEquals( $userinfo_2->sub, $saved_userinfo_2->sub );
		$this->assertEquals( $userinfo_2->name, $saved_userinfo_2->name );
		$this->assertEquals( $userinfo_2->email, $saved_userinfo_2->email );
	}

	/**
	 * Test that Auth0 meta data is created and updated properly.
	 */
	public function testGetAuth0Meta() {
		global $wpdb;

		$user      = $this->createUser();
		$uid       = $user->ID;
		$user_repo = new WP_Auth0_UsersRepo( self::$opts );
		$user_repo->update_auth0_object( $uid, $this->getUserinfo() );

		$this->assertEquals(
			get_user_meta( $uid, $wpdb->prefix . 'auth0_id', true ),
			WP_Auth0_UsersRepo::get_meta( $uid, 'auth0_id' )
		);
		$this->assertEquals(
			get_user_meta( $uid, $wpdb->prefix . 'auth0_obj', true ),
			WP_Auth0_UsersRepo::get_meta( $uid, 'auth0_obj' )
		);
		$this->assertEquals(
			get_user_meta( $uid, $wpdb->prefix . 'last_update', true ),
			WP_Auth0_UsersRepo::get_meta( $uid, 'last_update' )
		);
	}

	/**
	 * Test that Auth0 meta data is created and updated properly.
	 */
	public function testDeleteAuth0Meta() {
		global $wpdb;

		$user      = $this->createUser();
		$uid       = $user->ID;
		$user_repo = new WP_Auth0_UsersRepo( self::$opts );

		$userinfo = $this->getUserinfo();
		$user_repo->update_auth0_object( $uid, $userinfo );

		$this->assertNotEmpty( get_user_meta( $uid, $wpdb->prefix . 'last_update', true ) );
		$this->assertNotEmpty( get_user_meta( $uid, $wpdb->prefix . 'auth0_id', true ) );
		$this->assertNotEmpty( get_user_meta( $uid, $wpdb->prefix . 'auth0_obj', true ) );

		wp_auth0_delete_auth0_object( $uid );

		$this->assertEmpty( get_user_meta( $uid, $wpdb->prefix . 'last_update', true ) );
		$this->assertEmpty( get_user_meta( $uid, $wpdb->prefix . 'auth0_id', true ) );
		$this->assertEmpty( get_user_meta( $uid, $wpdb->prefix . 'auth0_obj', true ) );
	}
}
