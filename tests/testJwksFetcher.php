<?php
/**
 * Contains Class TestJwksFetcher.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class TestJwksFetcher.
 * Test the WP_Auth0_LoginManager::init_auth0() method.
 */
class TestJwksFetcher extends WP_Auth0_Test_Case {

	use HttpHelpers;

	use TokenHelper;

	public function setUp() {
		parent::setUp();
		$this->assertFalse( get_transient( 'WP_Auth0_JWKS_cache' ) );
	}

	public function testThatGetKeysUsesCache() {
		set_transient( 'WP_Auth0_JWKS_cache', [ '__test_key__' ], 3600 );
		$jwks = new WP_Auth0_JwksFetcher();

		$this->assertEquals( [ '__test_key__' ], $jwks->getKeys() );
	}

	public function testThatNotUsingCacheCallsEndpoint() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_jwks';

		set_transient( 'WP_Auth0_JWKS_cache', [ '__test_key__' ], 3600 );

		$this->assertEquals( [ '__test_key__' ], get_transient( 'WP_Auth0_JWKS_cache' ) );

		$jwks = new WP_Auth0_JwksFetcher();
		$keys = $jwks->getKeys( false );

		$this->assertEquals(
			[ '__test_kid_1__' => "-----BEGIN CERTIFICATE-----\n__test_x5c_1__\n-----END CERTIFICATE-----\n" ],
			$keys
		);

		$this->assertEquals( $keys, get_transient( 'WP_Auth0_JWKS_cache' ) );
	}

	public function testThatNonArrayCacheCallsEndpoint() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_jwks';

		set_transient( 'WP_Auth0_JWKS_cache', '__invalid_cache__', 3600 );
		$jwks = new WP_Auth0_JwksFetcher();

		$this->assertEquals(
			[ '__test_kid_1__' => "-----BEGIN CERTIFICATE-----\n__test_x5c_1__\n-----END CERTIFICATE-----\n" ],
			$jwks->getKeys()
		);
	}

	public function testThatInvalidJwksReturnsEmptyArray() {
		$this->startHttpMocking();
		$this->http_request_type = 'auth0_api_error';

		$jwks = new WP_Auth0_JwksFetcher();
		$this->assertEquals( [], $jwks->getKeys() );
	}

	public function testThatGetKeyPullsFromCache() {
		$this->startHttpMocking();
		$this->http_request_type = 'halt';

		set_transient(
			'WP_Auth0_JWKS_cache',
			[ '__test_kid_1__' => "-----BEGIN CERTIFICATE-----\n__test_x5c_1__\n-----END CERTIFICATE-----\n" ],
			3600
		);

		$jwks = new WP_Auth0_JwksFetcher();

		$this->assertEquals(
			"-----BEGIN CERTIFICATE-----\n__test_x5c_1__\n-----END CERTIFICATE-----\n",
			$jwks->getKey( '__test_kid_1__' )
		);
	}

	public function testThatGetKeyNotFoundForcesEndpointCall() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_jwks';

		set_transient( 'WP_Auth0_JWKS_cache', [ '__invalid_kid__' => '__invalid_x5c__' ], 3600 );

		$jwks      = new WP_Auth0_JwksFetcher();
		$found_x5c = $jwks->getKey( '__test_kid_1__' );

		$this->assertEquals( $found_x5c, $jwks->getKey( '__test_kid_1__' ) );
		$this->assertEquals( [ '__test_kid_1__' => $found_x5c ], get_transient( 'WP_Auth0_JWKS_cache' ) );
	}

	public function testThatNotFoundKidReturnsNull() {
		$this->startHttpMocking();
		$this->http_request_type = 'success_jwks';

		$jwks = new WP_Auth0_JwksFetcher();
		$this->assertNull( $jwks->getKey( '__not_found_kid__' ) );
	}

	public function testThatJwksFetcherUsesDefaultDomain() {
		$this->startHttpHalting();

		self::$opts->set('domain', 'test.auth0.com');
		$jwks = new WP_Auth0_JwksFetcher();

		try {
			$jwks->getKeys( false );
			$http_data = ['No exception caught'];
		} catch (Exception $e) {
			$http_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals('https://test.auth0.com/.well-known/jwks.json', $http_data['url']);
	}

	public function testThatJwksFetcherUsesCustomDomain() {
		$this->startHttpHalting();

		self::$opts->set('domain', 'test.auth0.com');
		self::$opts->set('custom_domain', 'custom.auth0.com');
		$jwks = new WP_Auth0_JwksFetcher();

		try {
			$jwks->getKeys( false );
			$http_data = ['No exception caught'];
		} catch (Exception $e) {
			$http_data = unserialize( $e->getMessage() );
		}

		$this->assertEquals('https://custom.auth0.com/.well-known/jwks.json', $http_data['url']);
	}
}
