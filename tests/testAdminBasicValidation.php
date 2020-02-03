<?php
/**
 * Contains Class TestAdminBasicValidation.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

/**
 * Class TestAdminBasicValidation.
 */
class TestAdminBasicValidation extends WP_Auth0_Test_Case {

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
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$admin  = new WP_Auth0_Admin_Basic( self::$opts );
		self::$fields = [
			'domain'                   => 'test.auth0.com',
			'custom_domain'            => '',
			'client_id'                => '__test_client_id__',
			'client_secret'            => '__test_client_secret__',
			'client_signing_algorithm' => WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG,
			'cache_expiration'         => '',
		];
	}

	public function testThatDomainIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [ 'domain' => 'test.auth0.com' ] );

		$this->assertEquals( 'test.auth0.com', $validated['domain'] );
	}

	public function testThatEmptyDomainIsAllowed() {
		$validated = self::$admin->basic_validation( [ 'domain' => '' ] );

		$this->assertEmpty( $validated['domain'] );
	}

	public function testThatHtmlIsRemovedFromDomain() {
		$validated = self::$admin->basic_validation( [ 'domain' => '<script>alert("hi")</script>test1.auth0.com' ] );

		$this->assertEquals( 'test1.auth0.com', $validated['domain'] );
	}

	public function testThatClientSecretIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [ 'client_secret' => '__test_client_secret__' ] );

		$this->assertEquals( '__test_client_secret__', $validated['client_secret'] );
	}

	public function testThatEmptyClientSecretIsAllowed() {
		$validated = self::$admin->basic_validation( [ 'client_secret' => '' ] );

		$this->assertEmpty( $validated['client_secret'] );
	}

	public function testThatHtmlIsRemovedFromClientSecret() {
		$validated = self::$admin->basic_validation( [ 'client_secret' => '<script>alert("hi")</script>__secret__' ] );

		$this->assertEquals( '__secret__', $validated['client_secret'] );
	}

	public function testThatUnchangedClientSecretIsKept() {
		self::$opts->set( 'client_secret', '__test_client_secret__' );
		$validated = self::$admin->basic_validation( [ 'client_secret' => '[REDACTED]' ] );

		$this->assertEquals( '__test_client_secret__', $validated['client_secret'] );
	}

	public function testThatValidAlgorithmIsValidatedProperly() {
		$validated = self::$admin->basic_validation( [ 'client_signing_algorithm' => 'HS256' ] );

		$this->assertEquals( 'HS256', $validated['client_signing_algorithm'] );

		$validated = self::$admin->basic_validation( [ 'client_signing_algorithm' => 'RS256' ] );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}

	public function testThatEmptyAlgorithmIsResetToDefault() {
		$validated = self::$admin->basic_validation( [ 'client_signing_algorithm' => '' ] );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}

	public function testThatInvalidAlgorithmIsResetToDefault() {
		$validated = self::$admin->basic_validation( [ 'client_signing_algorithm' => '__invalid_alg__' ] );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}
}
