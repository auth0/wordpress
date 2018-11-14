<?php
/**
 * Contains Class SuiteTemplate.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class SuiteTemplate.
 * Sample tests that can be copied and modified.
 */
class SuiteTemplate extends TestCase {


	use AjaxHelpers;

	use DomDocumentHelpers;

	use HookHelpers;

	use HttpHelpers;

	use RedirectHelpers;

	use SetUpTestDb;

	use UsersHelper;

	/**
	 * Instance of WP_Auth0_Options.
	 *
	 * @var WP_Auth0_Options
	 */
	public static $opts;

	/**
	 * WP_Auth0_ErrorLog instance.
	 *
	 * @var WP_Auth0_ErrorLog
	 */
	protected static $error_log;

	/**
	 * Setup for entire test class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$opts      = WP_Auth0_Options::Instance();
		self::$error_log = new WP_Auth0_ErrorLog();
	}

	/**
	 * Runs after each test method.
	 */
	public function setUp() {
		parent::setUp();

		$this->startAjaxHalting();
		$this->startAjaxReturn();

		$this->startHttpHalting();
		$this->startHttpMocking();

		$this->startRedirectHalting();
	}

	/**
	 * Runs after each test method.
	 */
	public function tearDown() {
		parent::tearDown();

		$this->stopAjaxHalting();
		$this->stopAjaxReturn();

		$this->stopHttpHalting();
		$this->stopHttpMocking();

		$this->stopRedirectHalting();

		self::$error_log->clear();
	}
}
