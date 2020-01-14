<?php
/**
 * Contains Class TestNonceHandler.
 *
 * @package WP-Auth0
 *
 * @since 3.11.2
 */

/**
 * Class TestNonceHandler.
 */
class TestNonceHandler extends WP_Auth0_Test_Case {

	/**
	 * @var WP_Auth0_Nonce_Handler
	 */
	public static $nonceHandler;

	/**
	 * @var \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount
	 */
	private static $mockSpyCookie;

	/**
	 * @var \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount
	 */
	private static $mockSpyHeader;

	/**
	 * Run after each test in this suite.
	 */
	public function tearDown() {
		parent::tearDown();
		self::$mockSpyCookie = null;
		self::$mockSpyHeader = null;
	}

	public function testDefaults() {
		$this->assertEquals( 'auth0_nonce', WP_Auth0_Nonce_Handler::NONCE_COOKIE_NAME );
		$this->assertEquals( 'auth0_nonce', WP_Auth0_Nonce_Handler::get_storage_cookie_name() );
		$this->assertEquals( 3600, WP_Auth0_Nonce_Handler::COOKIE_EXPIRES );
		$this->assertGreaterThanOrEqual( 32, strlen( WP_Auth0_Nonce_Handler::get_instance()->get_unique() ) );
	}

	public function testCookieNameFilter() {
		add_filter( 'auth0_nonce_cookie_name', [ $this, 'cookieFilter' ] );
		$this->assertEquals( '__test_custom_cookie_name__', WP_Auth0_Nonce_Handler::get_storage_cookie_name() );
		remove_filter( 'auth0_nonce_cookie_name', [ $this, 'cookieFilter' ] );
	}

	public function testThatValidateReturnsFalseAndCookieResetWhenNoCookie() {
		$mockNonceHandler = $this->getMock();
		$this->assertFalse( $mockNonceHandler->validate( uniqid() ) );
		$this->assertCount( 1, (array) self::$mockSpyCookie->getInvocations() );

		$setCookieParams = $this->getSpyParameters( self::$mockSpyCookie->getInvocations()[0] );

		$this->assertEquals( 'auth0_nonce', $setCookieParams[0] );
		$this->assertEquals( '', $setCookieParams[1] );
		$this->assertEquals( 0, $setCookieParams[2] );
	}

	public function testThatValidateReturnsFalseWhenCookieInvalid() {
		$mockNonceHandler      = $this->getMock();
		$_COOKIE['auth_nonce'] = '__invalid_nonce__';
		$this->assertFalse( $mockNonceHandler->validate( '__valid_nonce__' ) );
		$this->assertArrayNotHasKey( 'auth0_nonce', $_COOKIE );
	}

	public function testThatBackupCookieIsCheckedAndResetWhenImplicit() {
		self::$opts->set( 'auth0_implicit_workflow', 1 );
		$mockNonceHandler        = $this->getMock();
		$_COOKIE['auth0_nonce']  = '';
		$_COOKIE['_auth0_nonce'] = '__valid_nonce__';
		$this->assertTrue( $mockNonceHandler->validate( '__valid_nonce__' ) );
		$this->assertCount( 2, (array) self::$mockSpyCookie->getInvocations() );

		$setCookieParamsMain = $this->getSpyParameters( self::$mockSpyCookie->getInvocations()[1] );

		$this->assertEquals( 'auth0_nonce', $setCookieParamsMain[0] );
		$this->assertEquals( '', $setCookieParamsMain[1] );
		$this->assertEquals( 0, $setCookieParamsMain[2] );

		$setCookieParamsBackup = $this->getSpyParameters( self::$mockSpyCookie->getInvocations()[0] );

		$this->assertEquals( '_auth0_nonce', $setCookieParamsBackup[0] );
		$this->assertEquals( '', $setCookieParamsBackup[1] );
		$this->assertEquals( 0, $setCookieParamsBackup[2] );

		$this->assertArrayNotHasKey( '_auth0_nonce', $_COOKIE );
		$this->assertArrayNotHasKey( 'auth0_nonce', $_COOKIE );
	}

	public function testThatCookieValueIsSet() {
		$mockNonceHandler = $this->getMock();
		$mockNonceHandler->set_cookie( '__test_cookie_value__' );

		$this->assertEquals( '__test_cookie_value__', $_COOKIE['auth0_nonce'] );
		$this->assertArrayNotHasKey( '_auth0_nonce', $_COOKIE );
		$this->assertCount( 1, (array) self::$mockSpyCookie->getInvocations() );

		$setCookieParams = $this->getSpyParameters( self::$mockSpyCookie->getInvocations()[0] );

		$this->assertEquals( 'auth0_nonce', $setCookieParams[0] );
		$this->assertEquals( '__test_cookie_value__', $setCookieParams[1] );
		$this->assertGreaterThanOrEqual( time() + 3600, $setCookieParams[2] );
	}

	public function testThatMainAndBackupCookiesAreSetForImplicit() {
		self::$opts->set( 'auth0_implicit_workflow', 1 );
		$mockNonceHandler = $this->getMock();
		$mockNonceHandler->set_cookie( '__test_cookie_value__' );

		$this->assertEquals( '__test_cookie_value__', $_COOKIE['auth0_nonce'] );
		$this->assertEquals( '__test_cookie_value__', $_COOKIE['_auth0_nonce'] );
		$this->assertCount( 1, (array) self::$mockSpyCookie->getInvocations() );
		$this->assertCount( 1, (array) self::$mockSpyHeader->getInvocations() );

		$setCookieParams = $this->getSpyParameters( self::$mockSpyCookie->getInvocations()[0] );

		$this->assertEquals( '_auth0_nonce', $setCookieParams[0] );
		$this->assertEquals( '__test_cookie_value__', $setCookieParams[1] );
		$this->assertGreaterThanOrEqual( time() + 3600, $setCookieParams[2] );

		$setHeaderParams = $this->getSpyParameters( self::$mockSpyHeader->getInvocations()[0] );

		$this->assertEquals( 'auth0_nonce', $setHeaderParams[0] );
		$this->assertEquals( '__test_cookie_value__', $setHeaderParams[1] );
		$this->assertGreaterThanOrEqual( time() + 3600, $setHeaderParams[2] );
	}

	/*
	 * Helper methods
	 */

	public function getSpyParameters( $spyInvocation ) {
		if ( method_exists( $spyInvocation, 'getParameters' ) ) {
			return $spyInvocation->getParameters();
		} else {
			return $spyInvocation->parameters;
		}
	}

	/**
	 * @param array $args
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject|WP_Auth0_Nonce_Handler
	 */
	public function getMock( array $args = [] ) {
		$mockStore = $this->getMockBuilder( WP_Auth0_Nonce_Handler::class )
						  ->setMethods( [ 'write_cookie', 'write_cookie_header' ] )
						  ->getMock();

		$mockStore->expects( self::$mockSpyCookie = $this->any() )
				  ->method( 'write_cookie' )
				  ->willReturn( true );

		$mockStore->expects( self::$mockSpyHeader = $this->any() )
				  ->method( 'write_cookie_header' );

		return $mockStore;
	}

	public function cookieFilter() {
		return '__test_custom_cookie_name__';
	}
}
