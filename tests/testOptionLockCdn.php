<?php
/**
 * Contains Class TestOptionLockCdn.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class TestOptionLockCdn.
 * Tests that Advanced > Lock CDN URL works properly.
 */
class TestOptionLockCdn extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * WP_Auth0_Admin_Appearance instance.
	 *
	 * @var WP_Auth0_Admin_Appearance
	 */
	public static $admin;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$admin = new WP_Auth0_Admin_Appearance( self::$opts );
	}

	/**
	 * Test that the Custom CDN URLs are formatted properly.
	 */
	public function testThatCdnConstantsAreValidValues() {
		$this->assertEquals( WPA0_LOCK_CDN_URL, filter_var( WPA0_LOCK_CDN_URL, FILTER_VALIDATE_URL ) );
		$this->assertEquals( 'https://', substr( WPA0_LOCK_CDN_URL, 0, 8 ) );
	}

	/**
	 * Test that the Custom Lock CDN URL setting is output correctly.
	 */
	public function testThatCustomLockCdnFieldDisplaysProperly() {
		$field_args = [
			'label_for' => 'wpa0_custom_cdn_url',
			'opt_name'  => 'custom_cdn_url',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_custom_cdn_url( $field_args );
		$field_html = ob_get_clean();

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'input' );

		$this->assertContains( 'Currently using', $field_html );
		$this->assertContains( WPA0_LOCK_CDN_URL, $field_html );

		// Should only have one input field.
		$this->assertEquals( 1, $input->length );

		// Input should have the correct id attribute.
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );

		// Input should have the correct name attribute.
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);

		// Input should be a checkbox.
		$this->assertEquals( 'checkbox', $input->item( 0 )->getAttribute( 'type' ) );

		// Input should reference CDN URL field.
		$this->assertEquals( 'wpa0_cdn_url', $input->item( 0 )->getAttribute( 'data-expand' ) );

		// Set the setting to be on.
		self::$opts->set( $field_args['opt_name'], 1 );
		$this->assertEquals( 1, self::$opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		self::$admin->render_custom_cdn_url( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 1, $input->item( 0 )->getAttribute( 'value' ) );
		$this->assertNotContains( WPA0_LOCK_CDN_URL, $field_html );
	}

	/**
	 * Test that the Lock CDN URL setting is output correctly.
	 */
	public function testThatLockCdnUrlFieldDisplaysProperly() {
		$field_args = [
			'label_for' => 'wpa0_cdn_url',
			'opt_name'  => 'cdn_url',
		];

		// Get the field HTML.
		ob_start();
		self::$admin->render_cdn_url( $field_args );
		$field_html = ob_get_clean();

		// Check field HTML for required attributes.
		$input = $this->getDomListFromTagName( $field_html, 'input' );

		// Should only have one input field.
		$this->assertEquals( 1, $input->length );

		// Input should have the correct id attribute.
		$this->assertEquals( $field_args['label_for'], $input->item( 0 )->getAttribute( 'id' ) );

		// Input should have the correct name attribute.
		$this->assertEquals(
			self::OPTIONS_NAME . '[' . $field_args['opt_name'] . ']',
			$input->item( 0 )->getAttribute( 'name' )
		);

		// Input should be a text field.
		$this->assertEquals( 'text', $input->item( 0 )->getAttribute( 'type' ) );

		// Check that saving a custom domain appears in the field value.
		self::$opts->set( $field_args['opt_name'], 'https://auth0.com' );
		$this->assertEquals( 'https://auth0.com', self::$opts->get( $field_args['opt_name'] ) );

		// Get the field HTML.
		ob_start();
		self::$admin->render_cdn_url( $field_args );
		$field_html = ob_get_clean();

		$input = $this->getDomListFromTagName( $field_html, 'input' );
		$this->assertEquals( 'https://auth0.com', $input->item( 0 )->getAttribute( 'value' ) );
	}

	/**
	 * Test that the Custom Lock CDN URL setting is validated properly.
	 */
	public function testThatCustomLockCdnIsValidatedOnSave() {

		$validated = self::$admin->basic_validation( [ 'custom_cdn_url' => false ] );
		$this->assertEquals( false, $validated['custom_cdn_url'] );

		$validated = self::$admin->basic_validation( [ 'custom_cdn_url' => 0 ] );
		$this->assertEquals( false, $validated['custom_cdn_url'] );

		$validated = self::$admin->basic_validation( [ 'custom_cdn_url' => 1 ] );
		$this->assertEquals( true, $validated['custom_cdn_url'] );

		$validated = self::$admin->basic_validation( [ 'custom_cdn_url' => '1' ] );
		$this->assertEquals( true, $validated['custom_cdn_url'] );

		$validated = self::$admin->basic_validation( [ 'custom_cdn_url' => uniqid() ] );
		$this->assertEquals( false, $validated['custom_cdn_url'] );
	}

	/**
	 * Test that the Custom Lock CDN URL setting does not change the Lock CDN URL.
	 */
	public function testThatCustomLockCdnDoesNotChangeSavedCdnUrl() {

		$validated = self::$admin->basic_validation(
			[
				'cdn_url' => WPA0_LOCK_CDN_URL,
			]
		);
		$this->assertEquals( WPA0_LOCK_CDN_URL, $validated['cdn_url'] );

		$validated = self::$admin->basic_validation(
			[
				'custom_cdn_url' => '1',
				'cdn_url'        => WPA0_LOCK_CDN_URL,
			]
		);
		$this->assertEquals( WPA0_LOCK_CDN_URL, $validated['cdn_url'] );
	}

	/**
	 * Test that the Lock CDN URL setting is validated properly.
	 */
	public function testThatLockCdnUrlIsValidatedOnSave() {

		$validated = self::$admin->basic_validation(
			[ 'cdn_url' => WPA0_LOCK_CDN_URL ]
		);
		$this->assertEquals( WPA0_LOCK_CDN_URL, $validated['cdn_url'] );

		$validated = self::$admin->basic_validation( [ 'cdn_url' => ' ' . WPA0_LOCK_CDN_URL . ' ' ] );
		$this->assertEquals( WPA0_LOCK_CDN_URL, $validated['cdn_url'] );

		self::$opts->set( 'cdn_url', '__old_cdn_url__' );
		$validated = self::$admin->basic_validation(
			[
				'custom_cdn_url' => true,
				'cdn_url'        => '__invalid_cdn_url__',
			]
		);
		$this->assertEquals( '__old_cdn_url__', $validated['cdn_url'] );

		self::$opts->set( 'cdn_url', null );
		$validated = self::$admin->basic_validation(
			[
				'custom_cdn_url' => true,
				'cdn_url'        => '',
			]
		);
		$this->assertEquals( WPA0_LOCK_CDN_URL, $validated['cdn_url'] );
	}

	/**
	 * Test that the correct Lock URL is returned based on whether a custom URL is used or not.
	 */
	public function testThatLockUrlIsReturnedCorrectly() {
		self::$opts->set( 'cdn_url', 'https://auth0.com' );
		$this->assertEquals( WPA0_LOCK_CDN_URL, self::$opts->get_lock_url() );

		self::$opts->set( 'custom_cdn_url', true );
		$this->assertEquals( 'https://auth0.com', self::$opts->get_lock_url() );

		self::$opts->set( 'cdn_url', '' );
		$this->assertEquals( WPA0_LOCK_CDN_URL, self::$opts->get_lock_url() );
	}
}
