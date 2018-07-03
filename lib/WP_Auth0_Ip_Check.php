<?php
class WP_Auth0_Ip_Check {

	protected $valid_webtask_ips = array(
		'us' => '138.91.154.99,54.183.64.135,54.67.77.38,54.67.15.170,54.183.204.205,54.173.21.107,54.85.173.28,35.167.74.121,35.160.3.103,35.166.202.113,52.14.40.253,52.14.38.78,52.14.17.114,52.71.209.77,34.195.142.251,52.200.94.42',
		'eu' => '52.28.56.226,52.28.45.240,52.16.224.164,52.16.193.66',
		'au' => '52.64.84.177,52.64.111.197',
	);

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options = null ) {
		$this->a0_options = $a0_options;
	}

	public function get_ips_by_domain( $domain ) {
		if ( strpos( $domain, 'au.auth0.com' ) !== false ) {
			return $this->valid_webtask_ips['au'];
		} elseif ( strpos( $domain, 'eu.auth0.com' ) !== false ) {
			return $this->valid_webtask_ips['eu'];
		} elseif ( strpos( $domain, 'auth0.com' ) !== false ) {
			return $this->valid_webtask_ips['us'];
		}
		return null;
	}

	public function get_ip_by_region( $region ) {
		return $this->valid_webtask_ips[ $region ];
	}

	protected function get_request_ip() {
		$valid_proxy_ip = $this->a0_options->get( 'valid_proxy_ip' );

		if ( $valid_proxy_ip ) {
			if ( $_SERVER['REMOTE_ADDR'] == $valid_proxy_ip ) {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}

		return null;
	}

	protected function process_ip_list( $ip_list ) {
		$raw = explode( ',', $ip_list );

		$ranges = array();
		foreach ( $raw as $r ) {
			$d = explode( '-', $r );

			if ( count( $d ) < 2 ) {
				$ranges[] = array(
					'from' => trim( $d[0] ),
					'to'   => trim( $d[0] ),
				);
			} else {
				$ranges[] = array(
					'from' => trim( $d[0] ),
					'to'   => trim( $d[1] ),
				);
			}
		}
		return $ranges;
	}
	public function connection_is_valid( $valid_ips ) {
		$ip              = $this->get_request_ip();
		$valid_ip_ranges = $this->process_ip_list( $valid_ips );

		foreach ( $valid_ip_ranges as $range ) {
			$in_range = $this->in_range( $ip, $range );
			if ( $in_range ) {
				return true;
			}
		}

		return false;
	}


	// LEGACY
	public function init() {
		if ( ! WP_Auth0_Options::Instance()->get( 'ip_range_check' ) || is_admin() ) {
			return;
		}

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
		$ip     = $_SERVER['REMOTE_ADDR'];

		foreach ( $ranges as $range ) {
			$in_range = $this->in_range( $ip, $range );
			if ( $in_range ) {
				return true;
			}
		}

		return false;
	}

	private function in_range( $ip, $range ) {
		$from = ip2long( $range['from'] );
		$to   = ip2long( $range['to'] );
		$ip   = ip2long( $ip );

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
				$ranges[] = array(
					'from' => trim( $d[0] ),
					'to'   => trim( $d[0] ),
				);
			} else {
				$ranges[] = array(
					'from' => trim( $d[0] ),
					'to'   => trim( $d[1] ),
				);
			}
		}

		return $ranges;
	}
}
