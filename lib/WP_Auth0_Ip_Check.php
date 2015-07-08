<?php
class WP_Auth0_Ip_Check {
	public static function init() {
		if ( ! WP_Auth0_Options::Instance()->get( 'ip_range_check' ) || is_admin() ) {
			return;
		}

		new WP_Auth0_Ip_Check();
	}

	private function __construct() {
		add_filter( 'wp_auth0_get_option', array( $this, 'check_activate' ), 10, 2 );
	}

	public function check_activate( $val, $key ) {
		if ( 'active' !== $key ) {
			return $val;
		}
		$is_active = $this->validate_ip() ? 1 : 0;
		return $is_active;
	}

	private function validate_ip() {
		$ranges = $this->get_ranges();
		$ip = $_SERVER['REMOTE_ADDR'];

		foreach ( $ranges as $range ) {
			$in_range = $this->in_range( $ip, $range );
			if ( $in_range ) {
				return true;
			}
		}

		return false;
	}

	private function in_range($ip, $range) {
		$from = ip2long( $range['from'] );
		$to = ip2long( $range['to'] );
		$ip = ip2long( $ip );

		return $ip >= $from && $ip <= $to;
	}

	private function get_ranges() {
		$data = WP_Auth0_Options::Instance()->get( 'ip_ranges' );
		$data = str_replace( "\r\n", "\n", $data );

		$raw = explode( "\n", $data );

		$ranges = array();
		foreach ( $raw as $r ) {
			$d = explode( '-', $r );

			if ( count( $d ) < 2 ) {
				continue;
			}

			$ranges[] = array(
				'from' => trim( $d[0] ),
				'to' => trim( $d[1] ),
			);
		}

		return $ranges;
	}
}
