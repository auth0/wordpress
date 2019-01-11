<?php
/**
 * Contains Class TestUserRepoCreate.
 *
 * @package WP-Auth0
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestUserRepoCreate.
 * Tests that users are created and joined properly.
 */
class TestUserRepoCreate extends TestCase {

	use setUpTestDb;

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * Instance of WP_Auth0_UsersRepo.
	 *
	 * @var WP_Auth0_UsersRepo
	 */
	public static $repo;

	/**
	 * Dummy ID token to use during user creation.
	 *
	 * @var string
	 */
	const TOKEN = '__test_ID_token__';

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts = WP_Auth0_Options::Instance();
		self::$repo = new WP_Auth0_UsersRepo( self::$opts );
	}

	/**
	 * Test that a user without a verified email is rejected.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testRequiredEmailRejected() {
		$userinfo = $this->getUserinfo( 'auth0' );
		$this->createUser( [ 'user_email' => $userinfo->email ] );

		// Require a verified email.
		self::$opts->set( 'requires_verified_email', 1 );

		$this->expectException( WP_Auth0_EmailNotVerifiedException::class );
		self::$repo->create( $userinfo, self::TOKEN );
	}

	/**
	 * Test that an existing user with a different Auth0 sub is rejected.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testUserAlreadyExistsRejected() {
		$userinfo = $this->getUserinfo();
		$user     = $this->createUser( [ 'user_email' => $userinfo->email ] );
		self::$repo->update_auth0_object( $user->ID, $userinfo );

		// Require a verified email.
		self::$opts->set( 'requires_verified_email', 1 );

		// Modify the Auth0 sub so we get a mis-match.
		$userinfo->sub .= uniqid();

		// Make the email verified to pass the check.
		$userinfo->email_verified = 1;

		$this->expectException( WP_Auth0_CouldNotCreateUserException::class );
		self::$repo->create( $userinfo, self::TOKEN );
	}

	/**
	 * Test that signup is rejected if user registration is turned off (default).
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testSignupNotAllowedRejected() {
		$this->expectException( WP_Auth0_RegistrationNotEnabledException::class );
		self::$repo->create( $this->getUserinfo(), self::TOKEN );
	}

	/**
	 * Test that a core WP rejection happens with invalid new user data.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testUserCouldNotBeCreatedRejected() {
		$userinfo = $this->getUserinfo();

		// Crate a username that's too long (> 60 characters).
		$userinfo->username = uniqid() . uniqid() . uniqid() . uniqid() . uniqid();

		// Turn on user registration.
		update_option( 'users_can_register', 1 );

		// Do not equire a verified email.
		self::$opts->set( 'requires_verified_email', 1 );

		$this->expectException( WP_Auth0_CouldNotCreateUserException::class );
		self::$repo->create( $userinfo, self::TOKEN );
	}

	/**
	 * Test that the wpa0_should_create_user can prevent user signups.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testShouldCreateUserFilterRejected() {
		$userinfo = $this->getUserinfo();

		// Turn on user registration.
		update_option( 'users_can_register', 1 );

		add_filter(
			'wpa0_should_create_user',
			function() {
				return false;
			},
			10
		);

		$this->expectException( WP_Auth0_CouldNotCreateUserException::class );
		self::$repo->create( $userinfo, self::TOKEN );
	}

	/**
	 * Test that an Auth0 user can be joined with an existing WP one is email verification is turned off.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testJoinUserEmailVerificationOff() {
		$userinfo = $this->getUserinfo();
		$user     = $this->createUser( [ 'user_email' => $userinfo->email ] );

		// Require a verified email.
		self::$opts->set( 'requires_verified_email', 0 );

		$expected_uid = self::$repo->create( $userinfo, self::TOKEN );
		$this->assertEquals( $expected_uid, $user->ID );
	}

	/**
	 * Test that an Auth0 user can be joined with an existing WP one is email verification is turned on.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testJoinUserEmailVerified() {
		$userinfo = $this->getUserinfo();
		$user     = $this->createUser( [ 'user_email' => $userinfo->email ] );

		// Require a verified email.
		self::$opts->set( 'requires_verified_email', 1 );

		// Make the email verified to pass the check.
		$userinfo->email_verified = 1;

		$expected_uid = self::$repo->create( $userinfo, self::TOKEN );
		$this->assertEquals( $expected_uid, $user->ID );
	}

	/**
	 * Test that a new user can be created if their strategy is skipped.
	 *
	 * @throws WP_Auth0_CouldNotCreateUserException - WP registration rejected.
	 * @throws WP_Auth0_EmailNotVerifiedException - Verified email is required.
	 * @throws WP_Auth0_RegistrationNotEnabledException - User registration is turned off in WP.
	 */
	public function testJoinUserSkipStrategy() {
		$userinfo = $this->getUserinfo( 'auth0' );
		$user     = $this->createUser( [ 'user_email' => $userinfo->email ] );

		// Require a verified email.
		self::$opts->set( 'requires_verified_email', 1 );

		// Skip the auth0 strategy.
		self::$opts->set( 'skip_strategies', 'auth0' );

		$expected_uid = self::$repo->create( $userinfo, self::TOKEN );
		$this->assertEquals( $expected_uid, $user->ID );
	}

	/**
	 * Run after every test.
	 */
	public function tearDown() {
		parent::tearDown();
		update_option( 'users_can_register', null );
		self::$opts->set( 'requires_verified_email', null );
	}
}
