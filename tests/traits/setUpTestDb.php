<?php

/**
 * Trait SetUpTestDb.
 */
trait SetUpTestDb {

	/**
	 * Setup the database to be used for testing.
	 */
	public function setUp() {
		global $wpdb;
		$wpdb->suppress_errors = false;
		$wpdb->show_errors     = true;
		$wpdb->db_connect();
		ini_set( 'display_errors', 1 );
	}
}
