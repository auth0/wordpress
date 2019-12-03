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
			'domain'                    => 'test.auth0.com',
			'custom_domain'             => '',
			'client_id'                 => '__test_client_id__',
			'client_secret'             => '__test_client_secret__',
			'client_signing_algorithm'  => WP_Auth0_Api_Client::DEFAULT_CLIENT_ALG,
			'cache_expiration'          => '',
		];
	}

	public function testThatDomainIsValidatedProperly() {
		$old_input = array_merge( self::$fields, [ 'domain' => uniqid() ] );
		$validated = self::$admin->basic_validation( $old_input, self::$fields );

		$this->assertEquals( 'test.auth0.com', $validated['domain'] );
	}

	public function testThatEmptyDomainIsAllowed() {
		$old_input = array_merge( self::$fields, [ 'domain' => uniqid() ] );
		$new_input = array_merge( self::$fields, [ 'domain' => '' ] );
		$validated = self::$admin->basic_validation( $old_input, $new_input );

		$this->assertEmpty( $validated['domain'] );
	}

	public function testThatHtmlIsRemovedFromDomain() {
		$new_input = array_merge( self::$fields, [ 'domain' => '<script>alert("hi")</script>test1.auth0.com' ] );
		$validated = self::$admin->basic_validation( self::$fields, $new_input );

		$this->assertEquals( 'test1.auth0.com', $validated['domain'] );
	}

	public function testThatClientSecretIsValidatedProperly() {
		$old_input = array_merge( self::$fields, [ 'client_secret' => uniqid() ] );
		$validated = self::$admin->basic_validation( $old_input, self::$fields );

		$this->assertEquals( '__test_client_secret__', $validated['client_secret'] );
	}

	public function testThatEmptyClientSecretIsAllowed() {
		$old_input = array_merge( self::$fields, [ 'client_secret' => uniqid() ] );
		$new_input = array_merge( self::$fields, [ 'client_secret' => '' ] );
		$validated = self::$admin->basic_validation( $old_input, $new_input );

		$this->assertEmpty( $validated['client_secret'] );
	}

	public function testThatHtmlIsRemovedFromClientSecret() {
		$new_input = array_merge( self::$fields, [ 'client_secret' => '<script>alert("hi")</script>__secret__' ] );
		$validated = self::$admin->basic_validation( self::$fields, $new_input );

		$this->assertEquals( '__secret__', $validated['client_secret'] );
	}

	public function testThatUnchangedClientSecretIsKept() {
		$new_input = array_merge( self::$fields, [ 'client_secret' => '[REDACTED]' ] );
		$validated = self::$admin->basic_validation( self::$fields, $new_input );

		$this->assertEquals( '__test_client_secret__', $validated['client_secret'] );
	}

	public function testThatValidAlgorithmIsValidatedProperly() {
		$old_input = array_merge( self::$fields, [ 'client_signing_algorithm' => uniqid() ] );
		$new_input = array_merge( self::$fields, [ 'client_signing_algorithm' => 'HS256' ] );
		$validated = self::$admin->basic_validation( $old_input, $new_input );

		$this->assertEquals( 'HS256', $validated['client_signing_algorithm'] );

		$new_input['client_signing_algorithm'] = 'RS256';
		$validated                             = self::$admin->basic_validation( $old_input, $new_input );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}

	public function testThatEmptyAlgorithmIsResetToDefault() {
		$old_input = array_merge( self::$fields, [ 'client_signing_algorithm' => 'HS256' ] );
		$new_input = array_merge( self::$fields, [ 'client_signing_algorithm' => '' ] );
		$validated = self::$admin->basic_validation( $old_input, $new_input );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}

	public function testThatInvalidAlgorithmIsResetToDefault() {
		$old_input = array_merge( self::$fields, [ 'client_signing_algorithm' => 'HS256' ] );
		$new_input = array_merge( self::$fields, [ 'client_signing_algorithm' => uniqid() ] );
		$validated = self::$admin->basic_validation( $old_input, $new_input );

		$this->assertEquals( 'RS256', $validated['client_signing_algorithm'] );
	}
}
