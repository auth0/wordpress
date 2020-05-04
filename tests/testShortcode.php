<?php
/**
 * Contains Class TestShortcode.
 *
 * @package WP-Auth0
 *
 * @since 4.1.0
 */

/**
 * Class TestShortcode.
 */
class TestShortcode extends WP_Auth0_Test_Case {

	use HookHelpers;

	use WpScriptsHelper;

	use UsersHelper;

	private $existing_req_uri;

	public function setUp() {
		parent::setUp();
		$this->existing_req_uri = $_SERVER['REQUEST_URI'] ?? null;
	}
	public function tearDown() {
		parent::tearDown();
		$_SERVER['REQUEST_URI'] = $this->existing_req_uri;
	}

	public function testThatShortcodeEnqueuesLockScript() {
		$shortcode = wp_auth0_shortcode([]);

		$this->assertContains('<div id="auth0-login-form">', $shortcode);

		$script  = $this->getScript('wpa0_lock_init', 'wpAuth0LockGlobal');

		$this->assertEquals( WPA0_VERSION, $script->ver );
		$this->assertContains( 'jquery', $script->deps );
		$this->assertEquals( WPA0_PLUGIN_JS_URL . 'lock-init.js', $script->src );
	}

	public function testThatRedirectUsesPassedInValues() {
		wp_auth0_shortcode([ 'redirect_to' => '__test_atts_redirect__' ]);

		$script  = $this->getScript('wpa0_lock_init', 'wpAuth0LockGlobal');
		$localization      = $script->wpAuth0LockGlobal;

		$this->assertArrayHasKey('settings', $localization);
		$this->assertArrayHasKey('auth', $localization['settings']);
		$this->assertArrayHasKey('params', $localization['settings']['auth']);
		$this->assertArrayHasKey('state', $localization['settings']['auth']['params']);

		$state_decoded = base64_decode($localization['settings']['auth']['params']['state']);
		$state_decoded = json_decode($state_decoded);

		$this->assertEquals('__test_atts_redirect__', $state_decoded->redirect_to);
	}

	public function testThatRedirectUsesServerValueIfNotPassedIn() {
		$_SERVER['REQUEST_URI'] = '__test_server_req_uri__';
		wp_auth0_shortcode([]);

		$script  = $this->getScript('wpa0_lock_init', 'wpAuth0LockGlobal');
		$localization      = $script->wpAuth0LockGlobal;

		$state_decoded = base64_decode($localization['settings']['auth']['params']['state']);
		$state_decoded = json_decode($state_decoded);

		$this->assertEquals('http://example.org/__test_server_req_uri__', $state_decoded->redirect_to);
	}
}
