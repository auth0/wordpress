<?php
/**
 * Contains Class TestIdTokenValidator.
 *
 * @package WP-Auth0
 *
 * @since 3.11.0
 */

/**
 * Class TestIdTokenValidator.
 * Tests that ID tokens are validated properly.
 * Does not test the JWT class exhaustively, only WP_Auth0_Id_Token_Validator.
 */
class TestIdTokenValidator extends WP_Auth0_Test_Case {

	/**
	 * Test that an empty client_secret option fails validation.
	 * Client secret is empty by default when tests start.
	 */
	public function testThatJwtDecodeFailsWithEmptyKey() {
		$decoder = new WP_Auth0_Id_Token_Validator( uniqid(), self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Key may not be empty' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that an invalid secret in the token fails validation.
	 */
	public function testThatJwtDecodeFailsWithInvalidKey() {
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		$id_token = JWT::encode( [], '__test_invalid_secret__' );
		$decoder  = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Signature verification failed' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that an unsupported algorithm in the token fails validation.
	 */
	public function testThatJwtDecodeFailsWithInvalidAlg() {
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );

		$id_token = JWT::encode( [], '__test_valid_secret__', 'HS512' );
		$decoder  = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Algorithm not allowed' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that a missing issuer in the token fails validation.
	 */
	public function testThatJwtDecodeFailsWithMissingIss() {
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );

		$id_token = JWT::encode( [], '__test_valid_secret__', 'HS256' );
		$decoder  = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Invalid token issuer' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that an issuer in the token that does not match the stored domain fails validation.
	 */
	public function testThatJwtDecodeFailsWithInvalidIss() {
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		self::$opts->set( 'domain', 'valid.auth0.com' );

		$id_token = JWT::encode( [ 'iss' => 'https://invalid.auth0.com/' ], '__test_valid_secret__', 'HS256' );
		$decoder  = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Invalid token issuer' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that a missing audience in the token fails validation.
	 */
	public function testThatJwtDecodeFailsWithMissingAud() {
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		self::$opts->set( 'domain', 'valid.auth0.com' );

		$id_token = JWT::encode( [ 'iss' => 'https://valid.auth0.com/' ], '__test_valid_secret__', 'HS256' );
		$decoder  = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Invalid token audience' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that a token with an audience that does not match the stored client_id fails validation.
	 */
	public function testThatJwtDecodeFailsWithInvalidAud() {
		self::$opts->set( 'client_id', '__valid_audience__' );
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		self::$opts->set( 'domain', 'valid.auth0.com' );

		$id_token_payload = [
			'iss' => 'https://valid.auth0.com/',
			'aud' => '__invalid_audience__',
		];
		$id_token         = JWT::encode( $id_token_payload, '__test_valid_secret__', 'HS256' );
		$decoder          = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;
			$decoder->decode();
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Invalid token audience' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that a token without a nonce will fail validation.
	 */
	public function testThatJwtDecodeFailsWithMissingNonce() {
		self::$opts->set( 'client_id', '__valid_audience__' );
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		self::$opts->set( 'domain', 'valid.auth0.com' );

		$id_token_payload = [
			'iss' => 'https://valid.auth0.com/',
			'aud' => '__valid_audience__',
		];
		$id_token         = JWT::encode( $id_token_payload, '__test_valid_secret__', 'HS256' );
		$decoder          = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;

			// Suppress "Cannot modify header information" notice.
			// phpcs:ignore
			@$decoder->decode( true );
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Invalid token nonce' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that a token with an invalid nonce fails validation.
	 */
	public function testThatJwtDecodeFailsWithInvalidNonce() {
		self::$opts->set( 'client_id', '__valid_audience__' );
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		self::$opts->set( 'domain', 'valid.auth0.com' );
		$_COOKIE['auth0_nonce'] = '__valid_nonce__';

		$id_token_payload = [
			'iss'   => 'https://valid.auth0.com/',
			'aud'   => '__valid_audience__',
			'nonce' => '__invalid_nonce__',
		];
		$id_token         = JWT::encode( $id_token_payload, '__test_valid_secret__', 'HS256' );
		$decoder          = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		try {
			$caught_redirect = false;

			// Suppress "Cannot modify header information" notice.
			// phpcs:ignore
			@$decoder->decode( true );
		} catch ( WP_Auth0_InvalidIdTokenException $e ) {
			$caught_redirect = ( 'Invalid token nonce' === $e->getMessage() );
		}

		$this->assertTrue( $caught_redirect );
	}

	/**
	 * Test that a token with multiple audiences succeeds.
	 *
	 * @throws WP_Auth0_InvalidIdTokenException - If token is invalid.
	 */
	public function testThatJwtDecodeSucceedsWithMultipleAudiences() {
		self::$opts->set( 'client_id', '__valid_audience_1__' );
		self::$opts->set( 'client_secret', '__test_valid_secret__' );
		self::$opts->set( 'client_signing_algorithm', 'HS256' );
		self::$opts->set( 'domain', 'valid.auth0.com' );
		$_COOKIE['auth0_nonce'] = '__valid_nonce__';

		$id_token_payload = [
			'iss'   => 'https://valid.auth0.com/',
			'aud'   => [ '__valid_audience_1__', '__valid_audience_2__' ],
			'nonce' => '__valid_nonce__',
			'data'  => '__test_data__',
		];
		$id_token         = JWT::encode( $id_token_payload, '__test_valid_secret__', 'HS256' );
		$decoder          = new WP_Auth0_Id_Token_Validator( $id_token, self::$opts );

		// Suppress "Cannot modify header information" notice.
		// phpcs:ignore
		$decoded_token = @$decoder->decode( true );
		$this->assertEquals( '__test_data__', $decoded_token->data );
	}

	/**
	 * Test that the JWT leeway filter works.
	 */
	public function testThatJwtFilterChangesLeewayTimeUsed() {
		add_filter( 'auth0_jwt_leeway', [ self::class, 'jwtLeewayFilter' ], 10 );
		new WP_Auth0_Id_Token_Validator( uniqid(), self::$opts );
		remove_filter( 'auth0_jwt_leeway', [ self::class, 'jwtLeewayFilter' ], 10 );

		$this->assertEquals( 1234, JWT::$leeway );
	}

	/**
	 * Use this function to filter the JWT leeway.
	 *
	 * @return int
	 */
	public static function jwtLeewayFilter() {
		return 1234;
	}
}
