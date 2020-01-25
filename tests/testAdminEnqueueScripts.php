<?php
/**
 * Contains Class TestAdminEnqueueScripts.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestAdminEnqueueScripts.
 */
class TestAdminEnqueueScripts extends WP_Auth0_Test_Case {

	public function testThatAdminScriptsAreEnqueuedOnCorrectPage() {
		$this->assertFalse( wp_auth0_admin_enqueue_scripts() );

		$_REQUEST['page'] = uniqid();
		$this->assertFalse( wp_auth0_admin_enqueue_scripts() );
	}

	public function testThatAdminScriptIsRegisteredProperly() {
		$_REQUEST['page'] = 'wpa0';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$scripts = wp_scripts();
		$script  = $scripts->registered['wpa0_admin'];
		$this->assertEquals( WPA0_VERSION, $script->ver );
		$this->assertContains( 'jquery', $script->deps );
		$this->assertEquals( WPA0_PLUGIN_JS_URL . 'admin.js', $script->src );

		$localization_json = trim( str_replace( 'var wpa0 = ', '', $script->extra['data'] ), ';' );
		$localization      = json_decode( $localization_json, true );

		$this->assertEquals( 'Choose your icon', $localization['media_title'] );
		$this->assertEquals( 'Choose icon', $localization['media_button'] );
		$this->assertEquals( 'Working ...', $localization['ajax_working'] );
		$this->assertEquals( 'Done!', $localization['ajax_done'] );
		$this->assertEquals( 'Save or refresh this page to see changes.', $localization['refresh_prompt'] );
		$this->assertEquals( 'Are you sure?', $localization['form_confirm_submit_msg'] );
		$this->assertEquals( 1, wp_verify_nonce( $localization['clear_cache_nonce'], 'auth0_delete_cache_transient' ) );
		$this->assertEquals( 1, wp_verify_nonce( $localization['rotate_token_nonce'], 'auth0_rotate_migration_token' ) );
		$this->assertEquals( 'http://example.org/wp-admin/admin-ajax.php', $localization['ajax_url'] );
	}

	public function testThatAsyncScriptIsRegisteredProperly() {
		$_REQUEST['page'] = 'wpa0';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$scripts = wp_scripts();
		$script  = $scripts->registered['wpa0_async'];
		$this->assertEquals( WPA0_VERSION, $script->ver );
		$this->assertEmpty( $script->deps );
		$this->assertEquals( WPA0_PLUGIN_LIB_URL . 'async.min.js', $script->src );
	}

	public function testThatSetupStyleIsRegisteredProperly() {
		$_REQUEST['page'] = 'wpa0';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$styles = wp_styles();
		$style  = $styles->registered['wpa0_admin_initial_setup'];
		$this->assertEquals( WPA0_VERSION, $style->ver );
		$this->assertEmpty( $style->deps );
		$this->assertEquals( WPA0_PLUGIN_CSS_URL . 'initial-setup.css', $style->src );
	}

	public function testThatSettingsPageEnqueuesCorrectly() {
		$_REQUEST['page'] = 'wpa0';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$scripts = wp_scripts();
		$this->assertContains( 'wpa0_admin', $scripts->queue );
		$this->assertContains( 'wpa0_async', $scripts->queue );
		$this->assertContains( 'media-editor', $scripts->queue );

		$styles = wp_styles();
		$this->assertContains( 'wpa0_admin_initial_setup', $styles->queue );
		$this->assertContains( 'media', $styles->queue );
	}

	public function testThatSetupPageEnqueuesCorrectly() {
		$_REQUEST['page'] = 'wpa0-setup';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$scripts = wp_scripts();
		$this->assertContains( 'wpa0_admin', $scripts->queue );
		$this->assertContains( 'wpa0_async', $scripts->queue );
		$this->assertNotContains( 'media-editor', $scripts->queue );

		$styles = wp_styles();
		$this->assertContains( 'wpa0_admin_initial_setup', $styles->queue );
	}

	public function testThatImportPageEnqueuesCorrectly() {
		$_REQUEST['page'] = 'wpa0-import-settings';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$scripts = wp_scripts();
		$this->assertContains( 'wpa0_admin', $scripts->queue );
		$this->assertContains( 'wpa0_async', $scripts->queue );
		$this->assertNotContains( 'media-editor', $scripts->queue );

		$styles = wp_styles();
		$this->assertContains( 'wpa0_admin_initial_setup', $styles->queue );
	}

	public function testThatErrorsPageEnqueuesCorrectly() {
		$_REQUEST['page'] = 'wpa0-errors';
		$this->assertTrue( wp_auth0_admin_enqueue_scripts() );

		$scripts = wp_scripts();
		$this->assertContains( 'wpa0_admin', $scripts->queue );
		$this->assertContains( 'wpa0_async', $scripts->queue );
		$this->assertNotContains( 'media-editor', $scripts->queue );

		$styles = wp_styles();
		$this->assertContains( 'wpa0_admin_initial_setup', $styles->queue );
	}
}
