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
use Lcobucci\JWT\Token;

/**
 * Trait TokenHelper.
 */
trait TokenHelper {

	/**
	 * @param string $secret
	 * @param array $claims
	 *
	 * @return string
	 */
	public function makeToken($claims = [], $secret = '__test_secret__') {
		$builder = new Builder();

		foreach ($claims as $prop => $claim) {
			$builder->withClaim($prop, $claim);
		}

		return (string) $builder->getToken( new HsSigner(), new Key($secret));
	}
}
