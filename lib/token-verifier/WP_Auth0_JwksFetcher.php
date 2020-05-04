<?php

/**
 * Class JWKFetcher.
 *
 * @codeCoverageIgnore - Classes are adapted from the PHP SDK and tested there.
 */
class WP_Auth0_JwksFetcher {

	/**
	 * @var WP_Auth0_Options
	 */
	private $options;

	/**
	 * WP_Auth0_JwksFetcher constructor.
	 */
	public function __construct() {
		 $this->options = WP_Auth0_Options::Instance();
	}

	/**
	 * Convert a certificate to PEM format.
	 *
	 * @param string $cert X509 certificate to convert to PEM format.
	 *
	 * @return string
	 */
	protected function convertCertToPem( string $cert ) : string {
		$output  = '-----BEGIN CERTIFICATE-----' . PHP_EOL;
		$output .= chunk_split( $cert, 64, PHP_EOL );
		$output .= '-----END CERTIFICATE-----' . PHP_EOL;
		return $output;
	}

	/**
	 * Get a key using a kid.
	 *
	 * @param string $kid Key ID to get.
	 *
	 * @return string
	 */
	public function getKey( string $kid ) {
		$keys = $this->getKeys();

		if ( ! empty( $keys ) && empty( $keys[ $kid ] ) ) {
			$keys = $this->getKeys( false );
		}

		return $keys[ $kid ] ?? null;
	}

	/**
	 * Gets an array of keys from the JWKS as kid => x5c.
	 *
	 * @param bool $use_cache Defaults to true to use a cached value.
	 *
	 * @return array
	 */
	public function getKeys( $use_cache = true ) : array {
		$keys = $use_cache ? get_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME ) : [];
		if ( is_array( $keys ) && ! empty( $keys ) ) {
			return $keys;
		}

		$jwks = $this->requestJwks();

		if ( empty( $jwks ) || empty( $jwks['keys'] ) ) {
			return [];
		}

		$keys = [];
		foreach ( $jwks['keys'] as $key ) {
			if ( empty( $key['kid'] ) || empty( $key['x5c'] ) || empty( $key['x5c'][0] ) ) {
				continue;
			}

			$keys[ $key['kid'] ] = $this->convertCertToPem( $key['x5c'][0] );
		}

		$cache_expiration = $this->options->get( 'cache_expiration' );
		if ( $keys && $cache_expiration ) {
			set_transient( WPA0_JWKS_CACHE_TRANSIENT_NAME, $keys, $cache_expiration * MINUTE_IN_SECONDS );
		}

		return $keys;
	}

	/**
	 * Get a JWKS from a specific URL.
	 *
	 * @return array
	 */
	protected function requestJwks() : array {
		return ( new WP_Auth0_Api_Get_Jwks( $this->options, $this->options->get_auth_domain() ) )->call();
	}
}
