<?php
/**
 * Contains class WP_Auth0_Ip_Check.
 *
 * @package WP-Auth0
 *
 * @since 1.2.1
 */

/**
 * Class WP_Auth0_Ip_Check.
 * Used for checking IP addresses against whitelists and default Auth0 IPs.
 */
class WP_Auth0_Ip_Check {

	const IP_STRING_GLUE = ',';

	/**
	 * IP addresses for inbound connections per region.
	 * The list of IP addresses may be found at the footer section of the Custom Database Editor and the header for
	 * the Rules Editor.
	 * Updated 8/1/2018.
	 *
	 * @var array
	 *
	 * @link https://auth0.com/docs/rules/current#outbound-calls
	 */
	protected $valid_webtask_ips = array(
		'us' => array(
			'34.195.142.251',
			'35.160.3.103',
			'35.166.202.113',
			'35.167.74.121',
			'52.14.17.114',
			'52.14.38.78',
			'52.14.40.253',
			'52.71.209.77',
			'52.200.94.42',
			'54.67.15.170',
			'54.67.77.38',
			'54.85.173.28',
			'54.173.21.107',
			'54.183.64.135',
			'54.183.204.205',
			'138.91.154.99',
		),
		'eu' => array(
			'34.253.4.94',
			'35.156.51.163',
			'35.157.221.52',
			'52.16.193.66',
			'52.16.224.164',
			'52.28.45.240',
			'52.28.56.226',
			'52.28.184.187',
			'52.28.212.16',
			'52.29.176.99',
			'52.50.106.250',
			'52.57.230.214',
			'52.211.56.181',
			'52.213.38.246',
			'52.213.74.69',
			'52.213.216.142',
		),
		'au' => array(
			'13.54.254.182',
			'13.55.232.24',
			'13.210.52.131',
			'52.62.91.160',
			'52.63.36.78',
			'52.64.84.177',
			'52.64.111.197',
			'52.64.120.184',
			'54.66.205.24',
			'54.79.46.4',
			'54.153.131.0',
		),
	);

	/**
	 * Options object.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * WP_Auth0_Ip_Check constructor.
	 *
	 * @param WP_Auth0_Options|null $a0_options WP_Auth0_Options instance.
	 */
	public function __construct( WP_Auth0_Options $a0_options = null ) {
		$this->a0_options = $a0_options;
	}

	/**
	 * Get regional inbound IP addresses based on a domain.
	 *
	 * @param string $domain - Tenant domain.
	 * @param string $glue   - String used to implode arrays.
	 *
	 * @return string
	 */
	public function get_ips_by_domain( $domain = null, $glue = self::IP_STRING_GLUE ) {
		if ( empty( $domain ) ) {
			$domain = $this->a0_options->get( 'domain' );
		}
		$region = WP_Auth0::get_tenant_region( $domain );
		return $this->get_ip_by_region( $region, $glue );
	}

	/**
	 * Get regional inbound IP addresses based on a region.
	 *
	 * @param string $region - Tenant region.
	 * @param string $glue   - String used to implode arrays.
	 *
	 * @return string
	 */
	public function get_ip_by_region( $region, $glue = self::IP_STRING_GLUE ) {
		return implode( $glue, $this->valid_webtask_ips[ $region ] );
	}

	/**
	 * Get the IP address of the incoming connection.
	 *
	 * @return string
	 */
	protected function get_request_ip() {
		$valid_proxy_ip = $this->a0_options->get( 'valid_proxy_ip' );

		if ( $valid_proxy_ip ) {
			if ( $_SERVER['REMOTE_ADDR'] == $valid_proxy_ip ) {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Process an array or concatenated string of IP addresses into ranges.
	 *
	 * @param array|string $ip_list - IP list to process.
	 *
	 * @return array
	 */
	protected function process_ip_list( $ip_list ) {
		$raw = is_array( $ip_list ) ? $ip_list : explode( self::IP_STRING_GLUE, $ip_list );

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

	/**
	 * Check incoming IP address against default Auth0 and custom ones.
	 *
	 * @param string $valid_ips - String of comma-separated IP addresses to allow.
	 *
	 * @return bool
	 */
	public function connection_is_valid( $valid_ips = '' ) {
		$valid_ips   = explode( self::IP_STRING_GLUE, $valid_ips );
		$default_ips = explode( self::IP_STRING_GLUE, $this->get_ips_by_domain() );
		$allowed_ips = array_merge( $valid_ips, $default_ips );
		$allowed_ips = array_unique( $allowed_ips );

		foreach ( $this->process_ip_list( $allowed_ips ) as $range ) {
			if ( $this->in_range( $this->get_request_ip(), $range ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if an IP address is within a range.
	 *
	 * @param string $ip - IP address to check.
	 * @param array  $range - IP range to use.
	 *
	 * @return bool
	 */
	private function in_range( $ip, array $range ) {
		$from = ip2long( $range['from'] );
		$to   = ip2long( $range['to'] );
		$ip   = ip2long( $ip );

		return $ip >= $from && $ip <= $to;
	}

	// phpcs:disable

	/**
	 * TODO: Deprecate, not used. Also remove related setting.
	 *
	 * @codeCoverageIgnore
	 */
	public function init() {
		if ( ! WP_Auth0_Options::Instance()->get( 'ip_range_check' ) || is_admin() ) {
			return;
		}

		add_filter( 'wp_auth0_get_option', array( $this, 'check_activate' ), 10, 2 );
	}

	/**
	 * TODO: Deprecate, not used.
	 *
	 * @codeCoverageIgnore
	 */
	public function check_activate( $val, $key ) {
		if ( 'active' !== $key ) {
			return $val;
		}
		$is_active = $this->validate_ip() ? 1 : 0;
		return $is_active;
	}

	/**
	 * TODO: Deprecate, not used.
	 *
	 * @codeCoverageIgnore
	 */
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

	/**
	 * TODO: Deprecate, not used. Also remove related setting.
	 *
	 * @codeCoverageIgnore
	 */
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

	// phpcs:enable
}
