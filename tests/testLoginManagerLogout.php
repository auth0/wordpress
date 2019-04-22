<?php
/**
 * Contains Class TestLoginManagerLogout.
 *
 * @package WP-Auth0
 *
 * @since 3.10.0
 */

/**
 * Class TestLoginManagerLogout.
 * Test the WP_Auth0_LoginManager::logout() method.
 */
class TestLoginManagerLogout extends WP_Auth0_Test_Case {

	use DomDocumentHelpers;

	use RedirectHelpers;

	use UsersHelper;

	/**
	 * WP_Auth0_LoginManager instance to test.
	 *
	 * @var WP_Auth0_LoginManager
	 */
	protected $login;

	/**
	 * Runs before each test method.
	 */
	public function setUp() {
		parent::setUp();
		$this->login = new WP_Auth0_LoginManager( new WP_Auth0_UsersRepo( self::$opts ), self::$opts );
	}

	/**
	 *
	 */
	public function testThatNothingHappensIfNotReadyOrNotSsoSlo() {
		$this->assertNull( $this->login->logout() );
//		$_REQUEST['auth0'] = 1;
//		$this->assertFalse( $this->login->init_auth0() );
//		self::auth0Ready( true );
//		unset( $_REQUEST['auth0'] );
//		$this->assertFalse( $this->login->init_auth0() );
//
//		$output = '';
//		try {
//			$_REQUEST['auth0'] = 1;
//			$this->login->init_auth0();
//		} catch ( Exception $e ) {
//			$output = $e->getMessage();
//		}
//
//		$this->assertNotEmpty( $output );
	}
}
