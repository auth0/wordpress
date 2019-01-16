<?php
/**
 * Contains Class TestErrorLog.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class TestErrorLog.
 * Tests that the error log stores and displays properly.
 */
class TestErrorLog extends WP_Auth0_Test_Case {

	/**
	 * Test log entry section.
	 */
	const BASIC_LOG_ENTRY_SECTION = 'TestErrorLog::test_method()';

	/**
	 * Test log entry message.
	 */
	const BASIC_LOG_ENTRY_MESSAGE = '__test_error_message__';

	/**
	 * WP_Auth0_ErrorManager instance.
	 *
	 * @var WP_Auth0_ErrorManager
	 */
	public static $error_manager;

	/**
	 * Default log entry array.
	 *
	 * @var array
	 */
	public static $default_log_entry;

	/**
	 * Run once before this test case.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$error_manager     = new WP_Auth0_ErrorManager();
		self::$default_log_entry = [
			'section' => self::BASIC_LOG_ENTRY_SECTION,
			'code'    => 'unknown_code',
			'message' => self::BASIC_LOG_ENTRY_MESSAGE,
		];
	}

	/**
	 * Test that the error log option name did not change.
	 */
	public function testErrorLogOptionName() {
		$this->assertEquals( 'auth0_error_log', WP_Auth0_ErrorLog::OPTION_NAME );
	}

	/**
	 * Test that the error log default state is correct.
	 */
	public function testErrorLogEmpty() {
		$this->assertTrue( is_array( self::$error_log->get() ) );
		$this->assertEmpty( self::$error_log->get() );
	}

	/**
	 * Test that a basic added log entries are properly stored.
	 */
	public function testAddLogEntries() {
		$time      = time();
		$log_entry = self::$default_log_entry;
		self::$error_log->add( $log_entry );
		$log = self::$error_log->get();

		$this->assertCount( 1, $log );
		$this->assertEquals( self::BASIC_LOG_ENTRY_SECTION, $log[0]['section'] );
		$this->assertEquals( 'unknown_code', $log[0]['code'] );
		$this->assertEquals( self::BASIC_LOG_ENTRY_MESSAGE, $log[0]['message'] );
		$this->assertGreaterThanOrEqual( $time, $log[0]['date'] );
		$this->assertEquals( 1, $log[0]['count'] );

		// Modify the log entry and save.
		$log_entry['message'] = uniqid();
		self::$error_log->add( $log_entry );
		$log = self::$error_log->get();

		$this->assertCount( 2, $log );
		$this->assertEquals( $log_entry['message'], $log[0]['message'] );
		$this->assertEquals( self::BASIC_LOG_ENTRY_MESSAGE, $log[1]['message'] );
	}

	/**
	 * Test that a duplicate added log entry is properly stored.
	 */
	public function testAddDuplicateLogEntry() {
		self::$error_log->add( self::$default_log_entry );
		self::$error_log->add( self::$default_log_entry );
		$log = self::$error_log->get();

		$this->assertCount( 1, $log );
		$this->assertEquals( self::BASIC_LOG_ENTRY_SECTION, $log[0]['section'] );
		$this->assertEquals( self::BASIC_LOG_ENTRY_MESSAGE, $log[0]['message'] );
		$this->assertEquals( 2, $log[0]['count'] );
	}

	/**
	 * Test that the entry limit is enforced.
	 */
	public function testEntryLimit() {
		for ( $i = 1; $i <= 31; $i++ ) {
			self::$error_log->add(
				[
					'section' => uniqid(),
					'code'    => 'unknown_code',
					'message' => uniqid(),
				]
			);
		}
		$log = self::$error_log->get();

		$this->assertCount( 30, $log );
	}

	/**
	 * Test that a WP_Error log entry is properly inserted.
	 */
	public function testWpErrorLogEntryInsert() {
		$error_code = 999;
		$error_msg  = uniqid();
		$wp_error   = new WP_Error( $error_code, $error_msg );
		WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $wp_error );
		$log = self::$error_log->get();

		$this->assertCount( 1, $log );
		$this->assertEquals( __METHOD__, $log[0]['section'] );
		$this->assertEquals( $error_code, $log[0]['code'] );
		$this->assertEquals( $error_msg, $log[0]['message'] );
	}

	/**
	 * Test that an Exception log entry is properly inserted.
	 */
	public function testExceptionLogEntryInsert() {
		$error_code = 999;
		$error_msg  = uniqid();
		$exception  = new Exception( $error_msg, $error_code );
		WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $exception );
		$log = self::$error_log->get();

		$this->assertCount( 1, $log );
		$this->assertEquals( __METHOD__, $log[0]['section'] );
		$this->assertEquals( $error_code, $log[0]['code'] );
		$this->assertEquals( $error_msg, $log[0]['message'] );
	}

	/**
	 * Test that an Exception log entry is properly inserted.
	 */
	public function testResponseLogEntryInsert() {
		$error = [
			'response' => [
				'code'    => mt_rand( 111, 999 ),
				'message' => uniqid(),
			],
		];
		WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $error );
		$log = self::$error_log->get();

		$this->assertCount( 1, $log );
		$this->assertEquals( __METHOD__, $log[0]['section'] );
		$this->assertEquals( $error['response']['code'], $log[0]['code'] );
		$this->assertEquals( $error['response']['message'], $log[0]['message'] );
	}

	/**
	 * Test that an Exception log entry is properly inserted.
	 */
	public function testArrayLogEntryInsert() {
		$error = [
			'code'    => mt_rand( 111, 999 ),
			'message' => uniqid(),
		];
		WP_Auth0_ErrorManager::insert_auth0_error( __METHOD__, $error );
		$log = self::$error_log->get();

		$this->assertCount( 1, $log );
		$this->assertEquals( __METHOD__, $log[0]['section'] );
		$this->assertEquals( 'unknown_code', $log[0]['code'] );
		$this->assertEquals( serialize( $error ), $log[0]['message'] );
	}

	/**
	 * Test that log clearing works.
	 */
	public function testLogClear() {
		self::$error_log->add( self::$default_log_entry );
		$this->assertCount( 1, self::$error_log->get() );

		self::$error_log->clear();
		$log = get_option( WP_Auth0_ErrorLog::OPTION_NAME );

		$this->assertTrue( is_array( $log ) );
		$this->assertEmpty( $log );
	}

	/**
	 * Test that log deleting works.
	 */
	public function testLogDelete() {
		self::$error_log->add( self::$default_log_entry );
		$this->assertCount( 1, self::$error_log->get() );

		self::$error_log->delete();
		wp_cache_delete( WP_Auth0_ErrorLog::OPTION_NAME, 'options' );

		$this->assertFalse( get_option( WP_Auth0_ErrorLog::OPTION_NAME ) );
	}
}
