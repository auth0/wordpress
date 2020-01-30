<?php
/**
 * Contains Trait TokenHelper.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HsSigner;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsSigner;

/**
 * Trait TokenHelper.
 */
trait TokenHelper {

	/**
	 * Create an HS256 token for testing.
	 *
	 * @param array  $claims Claims to include in the payload.
	 * @param string $secret Signing key to use.
	 *
	 * @return string
	 */
	public function makeHsToken( $claims = [], $secret = '__test_secret__' ) {
		return (string) $this->buildToken( $claims )->getToken( new HsSigner(), new Key( $secret ) );
	}

	public function makeRsToken( $claims = [] ) {
		$pkey_resource = openssl_pkey_new(
			[
				'digest_alg'       => 'sha256',
				'private_key_type' => OPENSSL_KEYTYPE_RSA,
			]
		);

		openssl_pkey_export( $pkey_resource, $rsa_private_key );

		return (string) $this->buildToken( $claims )
			->withHeader( 'kid', '__test_kid_1__' )
			->getToken( new RsSigner(), new Key( $rsa_private_key ) );
	}

	public function buildToken( $claims ) {
		$builder = new Builder();

		foreach ( $claims as $prop => $claim ) {
			$builder->withClaim( $prop, $claim );
		}

		return $builder;
	}
}
