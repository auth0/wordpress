<?php
/**
 * Contains Class TestFilterGetAvatar.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

class FilterGetAvatarTest extends WP_Auth0_Test_Case {

	use HookHelpers;

	use UsersHelper;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$users_repo = new WP_Auth0_UsersRepo( self::$opts );
	}

	public function testThatFilterAvatarHookIsAdded() {
		$expect_hooked = [
			'wp_auth0_filter_get_avatar' => [
				'priority'      => 1,
				'accepted_args' => 5,
			],
		];
		$this->assertHookedFunction( 'get_avatar', $expect_hooked );
	}

	public function testThatAvatarIsNotFilteredIfSettingIsOff() {
		self::$opts->set( 'override_wp_avatars', false );
		$this->assertEquals(
			'__test_avatar__',
			wp_auth0_filter_get_avatar( '__test_avatar__', uniqid(), uniqid(), uniqid(), uniqid() )
		);
	}

	public function testThatAvatarIsNotFilteredIfUserNotFound() {
		self::$opts->set( 'override_wp_avatars', true );
		$this->assertEquals(
			'__test_avatar__',
			wp_auth0_filter_get_avatar( '__test_avatar__', '__invalid_user__', uniqid(), uniqid(), uniqid() )
		);
	}

	public function testThatAvatarIsNotFilteredIfAuth0ProfileNotFound() {
		self::$opts->set( 'override_wp_avatars', true );
		$this->assertEquals(
			'__test_avatar__',
			wp_auth0_filter_get_avatar( '__test_avatar__', 1, uniqid(), uniqid(), uniqid() )
		);
	}

	public function testThatAvatarIsNotFilteredIfAuth0ProfileImageNotAvailable() {
		self::$opts->set( 'override_wp_avatars', true );
		$this->storeAuth0Data( 1 );
		$this->assertEquals(
			'__test_avatar__',
			wp_auth0_filter_get_avatar( '__test_avatar__', 1, uniqid(), uniqid(), uniqid() )
		);
	}

	public function testThatAvatarIsFilteredIfUserId() {
		self::$opts->set( 'override_wp_avatars', true );
		$userinfo = (object) [
			'picture' => '__picture__',
			'sub'     => uniqid(),
		];
		self::$users_repo->update_auth0_object( 1, $userinfo );
		$this->assertEquals(
			'<img alt="alt" src="http://__picture__" class="avatar avatar-5 photo avatar-auth0" width="5" height="5"/>',
			wp_auth0_filter_get_avatar( '__test_avatar__', 1, 5, uniqid(), 'alt' )
		);
	}

	public function testThatAvatarIsFilteredIfWpUser() {
		self::$opts->set( 'override_wp_avatars', true );
		$userinfo = (object) [
			'picture' => '__picture__',
			'sub'     => uniqid(),
		];
		self::$users_repo->update_auth0_object( 1, $userinfo );
		$user = new WP_User( 1 );
		$this->assertEquals(
			'<img alt="alt" src="http://__picture__" class="avatar avatar-6 photo avatar-auth0" width="6" height="6"/>',
			wp_auth0_filter_get_avatar( '__test_avatar__', $user, 6, uniqid(), 'alt' )
		);
	}

	public function testThatAvatarIsFilteredIfEmail() {
		self::$opts->set( 'override_wp_avatars', true );
		$userinfo = (object) [
			'picture' => '__picture__',
			'sub'     => uniqid(),
		];
		self::$users_repo->update_auth0_object( 1, $userinfo );
		$this->assertEquals(
			'<img alt="alt" src="http://__picture__" class="avatar avatar-7 photo avatar-auth0" width="7" height="7"/>',
			wp_auth0_filter_get_avatar( '__test_avatar__', 'admin@example.org', 7, uniqid(), 'alt' )
		);
	}

	public function testThatAvatarIsFilteredIfWpPost() {
		self::$opts->set( 'override_wp_avatars', true );
		$userinfo = (object) [
			'picture' => '__picture__',
			'sub'     => uniqid(),
		];
		self::$users_repo->update_auth0_object( 1, $userinfo );
		$post_id = wp_insert_post(
			[
				'post_author' => 1,
				'post_title'  => uniqid(),
			]
		);
		$post    = WP_Post::get_instance( $post_id );
		$this->assertEquals(
			'<img alt="alt" src="http://__picture__" class="avatar avatar-8 photo avatar-auth0" width="8" height="8"/>',
			wp_auth0_filter_get_avatar( '__test_avatar__', $post, 8, uniqid(), 'alt' )
		);
	}
}
