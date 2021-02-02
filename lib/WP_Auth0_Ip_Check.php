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
	protected $valid_webtask_ips = [
		'us' => [
			'3.211.189.167',
			'18.233.90.226',
			'34.195.142.251',
			'35.160.3.103',
			'35.166.202.113',
			'35.167.74.121',
			'35.171.156.124',
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
		],
		'eu' => [
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
			'52.208.95.174',
			'52.210.122.50',
			'52.211.56.181',
			'52.213.38.246',
			'52.213.74.69',
			'52.213.216.142',
			'54.76.184.103',
		],
		'au' => [
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
		],
	];

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
	 * @return string|array
	 */
	public function get_ips_by_domain( $domain = null, $glue = self::IP_STRING_GLUE ) {
		if ( empty( $domain ) ) {
			$domain = $this->a0_options->get( 'domain' );
		}
		$region = wp_auth0_get_tenant_region( $domain );
		return $this->get_ip_by_region( $region, $glue );
	}

	/**
	 * Get regional inbound IP addresses based on a region.
	 *
	 * @param string $region - Tenant region.
	 * @param string|null $glue   - String used to implode arrays.
	 *
	 * @return string|array
	 */
	public function get_ip_by_region( $region, $glue = self::IP_STRING_GLUE ) {
		$ip_addresses = $this->valid_webtask_ips[ $region ];
		return is_null( $glue ) ? $ip_addresses : implode( $glue, $ip_addresses );
	}

	/**
	 * Get the IP address of the incoming connection.
	 *
	 * @return string
	 */
	protected function get_request_ip() {
		$valid_proxy_ip = $this->a0_options->get( 'valid_proxy_ip' );

		// Null coalescing validates the input variable.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$remote_addr = $_SERVER['REMOTE_ADDR'] ?? null;

		if ( $valid_proxy_ip && $remote_addr === $valid_proxy_ip ) {

			// Null coalescing validates the input variable.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $remote_addr;
		}

		return $remote_addr;
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

		$ranges = [];
		foreach ( $raw as $r ) {
			$d = explode( '-', $r );

			if ( count( $d ) < 2 ) {
				$ranges[] = [
					'from' => trim( $d[0] ),
					'to'   => trim( $d[0] ),
				];
			} else {
				$ranges[] = [
					'from' => trim( $d[0] ),
					'to'   => trim( $d[1] ),
				];
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
}
