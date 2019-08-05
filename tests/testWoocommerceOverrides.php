<?php
/**
 * Contains Class TestWoocommerceOverrides.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestWoocommerceOverrides.
 * Tests the WP_Auth0_WooCommerceOverrides class.
 */
class TestWoocommerceOverrides extends WP_Auth0_Test_Case {

	use HookHelpers;

	public function testThatWooCheckoutHookIsSet() {
		$expect_hooked = [
			'wp_auth0_filter_woocommerce_checkout_login_message' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'woocommerce_checkout_login_message', $expect_hooked );
	}

	public function testThatWooAccountHookIsSet() {
		$expect_hooked = [
			'wp_auth0_filter_woocommerce_before_customer_login_form' => [
				'priority'      => 10,
				'accepted_args' => 1,
			],
		];
		$this->assertHookedFunction( 'woocommerce_before_customer_login_form', $expect_hooked );
	}

	public function testThatUnchangedHtmlIsReturnedFromCheckoutHookIfPluginNotReady() {
		$returned = wp_auth0_filter_woocommerce_checkout_login_message( '__original_text__' );
		$this->assertEquals( '__original_text__', $returned );
	}

	public function testThatUnchangedHtmlIsReturnedFromAccountHookIfPluginNotReady() {
		$returned = wp_auth0_filter_woocommerce_before_customer_login_form( '__original_text__' );
		$this->assertEquals( '__original_text__', $returned );
	}
}
