<?php
/**
 * Contains Class TestWpAjaxHooks.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestWpAjaxHooks.
 * Tests that AJAX hooks work as expected.
 */
class TestWpAjaxHooks extends WP_Auth0_Test_Case {

	use AjaxHelpers;

	use HookHelpers;

	use UsersHelper;

	public function testThatTokenRotateAjaxHookIsSet() {
		$expect_hooked = [
			'wp_auth0_ajax_rotate_migration_token' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'wp_ajax_auth0_rotate_migration_token', $expect_hooked );
	}

	public function testThatAjaxTokenRotationFailsWithBadNonce() {
		$this->startAjaxHalting();

		$caught_exception = false;
		try {
			$_REQUEST['_ajax_nonce'] = uniqid();
			wp_auth0_ajax_rotate_migration_token();
		} catch ( Exception $e ) {
			$caught_exception = ( 'bad_nonce' === $e->getMessage() );
		}
		$this->assertTrue( $caught_exception );
	}

	public function testThatAjaxTokenRotationFailsIfNotAnAdmin() {
		$this->startAjaxReturn();

		$old_token = uniqid();
		self::$opts->set( 'migration_token', $old_token );

		ob_start();
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'auth0_rotate_migration_token' );
		wp_auth0_ajax_rotate_migration_token();
		$return_json = explode( PHP_EOL, ob_get_clean() );

		$this->assertEquals( '{"success":false,"data":{"error":"Not authorized."}}', end( $return_json ) );
	}

	public function testThatAjaxTokenRotationSavesNewToken() {
		$this->startAjaxReturn();

		$old_token = uniqid();
		self::$opts->set( 'migration_token', $old_token );
		$this->setGlobalUser();

		ob_start();
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'auth0_rotate_migration_token' );
		wp_auth0_ajax_rotate_migration_token();
		$return_json = explode( PHP_EOL, ob_get_clean() );

		$this->assertEquals( '{"success":true}', end( $return_json ) );
		$this->assertNotEquals( $old_token, self::$opts->get( 'migration_token' ) );
		$this->assertGreaterThanOrEqual( 64, strlen( self::$opts->get( 'migration_token' ) ) );
	}

}
