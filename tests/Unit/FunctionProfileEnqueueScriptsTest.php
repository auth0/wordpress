<?php
/**
 * Contains Class TestFunctionProfileEnqueueScripts.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

class FunctionProfileEnqueueScriptsTest extends WP_Auth0_Test_Case {

	public function testThatNotProfilePageDoesNotLoadScripts() {
		global $pagenow;

		$this->assertNotContains( 'profile.php', $pagenow );
		$this->assertNotContains( 'user-edit.php', $pagenow );
		$this->assertFalse( wp_auth0_profile_enqueue_scripts() );
	}

	public function testThatProfilePageLoadsScripts() {
		global $pagenow, $user_id;
		$pagenow = 'profile.php';
		$user_id = 1;

		$this->assertTrue( wp_auth0_profile_enqueue_scripts() );

		$scripts = wp_scripts();
		$this->assertContains( 'wpa0_user_profile', $scripts->queue );

		$profile_script = $scripts->registered['wpa0_user_profile'];
		$this->assertEquals( WPA0_VERSION, $profile_script->ver );
		$this->assertContains( 'jquery', $profile_script->deps );
		$this->assertContains( 'assets/js/edit-user-profile.js', $profile_script->src );

		$localization_json = trim( str_replace( 'var wpa0UserProfile = ', '', $profile_script->extra['data'] ), ';' );
		$localization      = json_decode( $localization_json, true );

		$this->assertEquals( 1, $localization['userId'] );
		$this->assertEmpty( $localization['userStrategy'] );
		$this->assertEquals( 1, wp_verify_nonce( $localization['deleteIdNonce'], 'delete_auth0_identity' ) );
		$this->assertEquals( 'http://example.org/wp-admin/admin-ajax.php', $localization['ajaxUrl'] );
		$this->assertArrayHasKey( 'confirmDeleteId', $localization['i18n'] );
		$this->assertArrayHasKey( 'actionComplete', $localization['i18n'] );
		$this->assertArrayHasKey( 'actionFailed', $localization['i18n'] );
		$this->assertArrayHasKey( 'cannotChangeEmail', $localization['i18n'] );
	}
}
