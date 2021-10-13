<?php
/**
 * Contains Class TestAdminBasicValidation.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

class AdminBasicValidationTest extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	/**
	 * WP_Auth0_Admin_Basic instance.
	 *
	 * @var WP_Auth0_Admin_Basic
	 */
	public static $admin;

	/**
	 * All required basic settings fields.
	 *
	 * @var array
	 */
	public static $fields;

	/**
	 * Run before the test suite starts.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$admin = new WP_Auth0_Admin( self::$opts, new WP_Auth0_Routes( self::$opts ) );
	}

	public function testThatDomainIsValidatedProperly() {
		$validated = self::$admin->input_validator( [ 'domain' => 'test.auth0.com' ] );

		$this->assertEquals( 'test.auth0.com', $validated['domain'] );
	}

	public function testThatEmptyDomainIsAllowed() {
		$validated = self::$admin->input_validator( [ 'domain' => '' ] );

		$this->assertEmpty( $validated['domain'] );
	}

	public function testThatHtmlIsRemovedFromDomain() {
		$validated = self::$admin->input_validator( [ 'domain' => '<script>alert("hi")</script>test1.auth0.com' ] );

		$this->assertEquals( 'test1.auth0.com', $validated['domain'] );
	}

	public function testThatClientSecretIsValidatedProperly() {
		$validated = self::$admin->input_validator( [ 'client_secret' => '__test_client_secret__' ] );

		$this->assertEquals( '__test_client_secret__', $validated['client_secret'] );
	}

	public function testThatEmptyClientSecretIsAllowed() {
		$validated = self::$admin->input_validator( [ 'client_secret' => '' ] );

		$this->assertEmpty( $validated['client_secret'] );
	}

	public function testThatHtmlIsRemovedFromClientSecret() {
		$validated = self::$admin->input_validator( [ 'client_secret' => '<script>alert("hi")</script>__secret__' ] );

		$this->assertEquals( '__secret__', $validated['client_secret'] );
	}

	public function testThatUnchangedClientSecretIsKept() {
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		$validated = self::$admin->input_validator( [ 'client_secret' => '[REDACTED]' ] );

		$this->assertEquals( '__test_client_secret__', $validated['client_secret'] );
	}

	public function testThatValidAlgorithmIsValidatedProperly() {
		$validated = self::$admin->input_validator( [ 'client_signing_algorithm' => 'HS256' ] );

		$this->assertEquals( 'HS256', $validated['client_signing_algorithm'] );

		$validated = self::$admin->input_validator( [ 'client_signing_algorithm' => 'RS256' ] );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}

	public function testThatEmptyAlgorithmIsResetToDefault() {
		$validated = self::$admin->input_validator( [ 'client_signing_algorithm' => '' ] );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}

	public function testThatInvalidAlgorithmIsResetToDefault() {
		$validated = self::$admin->input_validator( [ 'client_signing_algorithm' => '__invalid_alg__' ] );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}
}
