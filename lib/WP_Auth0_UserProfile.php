<?php

class WP_Auth0_UserProfile {

	protected $raw;
	protected $profile;
	protected $geoip;

	public function __construct( $str ) {
		$this->raw     = $str;
		$this->profile = WP_Auth0_Serializer::unserialize( $str );
	}

	public function get() {
		return array(
			'gender'     => $this->get_gender(),
			'age'        => $this->get_age(),
			'created_at' => $this->get_created_at(),
			'idp'        => $this->get_idp(),
			'location'   => array(
				'latitude'  => $this->get_latitude(),
				'longitude' => $this->get_longitude(),
			),
			'zipcode'    => $this->get_zipcode(),
			'income'     => $this->get_income(),
		);
	}

	public function get_email() {
		return isset( $this->profile->email ) ? $this->profile->email : null;
	}

	public function get_nickname() {
		return isset( $this->profile->nickname ) ? $this->profile->nickname : null;
	}

	public function get_name() {
		return isset( $this->profile->name ) ? $this->profile->name : null;
	}

	public function get_givenname() {
		return isset( $this->profile->givenname ) ? $this->profile->givenname : null;
	}

	public function get_last_login() {
		return isset( $this->profile->last_login ) ? $this->profile->last_login : null;
	}

	public function get_logins_count() {
		return isset( $this->profile->logins_count ) ? $this->profile->logins_count : null;
	}

	public function get_created_at() {
		return isset( $this->profile->created_at ) ? $this->profile->created_at : null;
	}

	public function get_gender() {
		if ( isset( $this->profile->gender ) ) {
			$genderName = strtolower( $this->profile->gender );
		} elseif ( isset( $this->profile->user_metadata ) && isset( $this->profile->user_metadata->fullContactInfo ) && isset( $this->profile->user_metadata->fullContactInfo->demographics ) && isset( $this->profile->user_metadata->fullContactInfo->demographics->gender ) ) {
			$genderName = strtolower( $this->profile->user_metadata->fullContactInfo->demographics->gender );
		} elseif ( isset( $this->profile->app_metadata ) && isset( $this->profile->app_metadata->fullContactInfo ) && isset( $this->profile->app_metadata->fullContactInfo->demographics ) && isset( $this->profile->user_metadata->fullContactInfo->demographics->gender ) ) {
			$genderName = strtolower( $this->profile->app_metadata->fullContactInfo->demographics->gender );
		} else {
			$genderName = null;
		}

		return $genderName;
	}

	public function get_geoip() {

		if ( $this->geoip ) {
			return $this->geoip;
		}

		if ( isset( $this->profile->app_metadata ) && isset( $this->profile->app_metadata->geoip ) ) {
			$this->geoip = $this->profile->app_metadata->geoip;
		}
		if ( isset( $this->profile->user_metadata ) && isset( $this->profile->user_metadata->geoip ) ) {
			$this->geoip = $this->profile->user_metadata->geoip;
		}

		return $this->geoip;
	}

	public function get_latitude() {
		$geoip = $this->get_geoip();
		if ( ! $geoip ) {
			return null;
		}
		if ( ! isset( $geoip->latitude ) ) {
			return null;
		}

		return $geoip->latitude;
	}

	public function get_longitude() {
		$geoip = $this->get_geoip();

		if ( ! $geoip ) {
			return null;
		}
		if ( ! isset( $geoip->longitude ) ) {
			return null;
		}

		return $geoip->longitude;
	}

	public function get_zipcode() {
		$geoip = $this->get_geoip();

		if ( ! $geoip ) {
			return null;
		}
		if ( ! isset( $geoip->postal_code ) ) {
			return null;
		}

		return $geoip->postal_code;
	}

	public function get_country_code() {
		$geoip = $this->get_geoip();

		if ( ! $geoip ) {
			return null;
		}
		if ( ! isset( $geoip->country_code ) ) {
			return null;
		}

		return $geoip->country_code;
	}

	public function get_idp() {

		if ( ! isset( $this->profile->identities ) ) {
			return array();
		}

		$idPs = array();
		foreach ( $this->profile->identities as $identity ) {

			$idPs[] = $identity->provider;

		}
		return $idPs;
	}

	public function get_country_name() {
		$geoip = $this->get_geoip();

		if ( ! $geoip ) {
			return null;
		}
		if ( ! isset( $geoip->country_name ) ) {
			return null;
		}

		return $geoip->country_name;
	}


	public function get_income() {
		$income = null;
		if ( isset( $this->profile->app_metadata->zipcode_income ) ) {
			$income = $this->profile->app_metadata->zipcode_income;
		} elseif ( isset( $this->profile->user_metadata->zipcode_income ) ) {
			$income = $this->profile->user_metadata->zipcode_income;
		}
		return $income;
	}

	public function get_age() {
		if ( isset( $this->profile->age ) ) {
			return $this->profile->age;
		}
		if ( isset( $this->profile->user_metadata ) && isset( $this->profile->user_metadata->fullContactInfo ) && isset( $this->profile->user_metadata->fullContactInfo->age ) ) {
			return $this->profile->user_metadata->fullContactInfo->age;
		}
		if ( isset( $this->profile->app_metadata ) && isset( $this->profile->app_metadata->fullContactInfo ) && isset( $this->profile->app_metadata->fullContactInfo->age ) ) {
			return $this->profile->app_metadata->fullContactInfo->age;
		}
		if ( isset( $this->profile->user_metadata ) && isset( $this->profile->user_metadata->fullContactInfo ) && isset( $this->profile->user_metadata->fullContactInfo->demographics ) && isset( $this->profile->user_metadata->fullContactInfo->demographics->age ) ) {
			return $this->profile->user_metadata->fullContactInfo->demographics->age;
		}
		if ( isset( $this->profile->app_metadata ) && isset( $this->profile->app_metadata->fullContactInfo ) && isset( $this->profile->app_metadata->fullContactInfo->demographics ) && isset( $this->profile->user_metadata->fullContactInfo->demographics->age ) ) {
			return $this->profile->user_metadata->app_metadata->demographics->age;
		}

		if ( isset( $this->profile->user_metadata ) && isset( $this->profile->user_metadata->fullContactInfo ) && isset( $this->profile->user_metadata->fullContactInfo->birthDate ) ) {
			$birthDate = explode( '-', $this->profile->user_metadata->fullContactInfo->birthDate );

			$age = ( date( 'md', date( 'U', mktime( 0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0] ) ) ) > date( 'md' )
				? ( ( date( 'Y' ) - $birthDate[0] ) - 1 )
				: ( date( 'Y' ) - $birthDate[0] ) );
			return $age;
		}
		if ( isset( $this->profile->app_metadata ) && isset( $this->profile->app_metadata->fullContactInfo ) && isset( $this->profile->app_metadata->fullContactInfo->birthDate ) ) {
			$birthDate = explode( '-', $this->profile->app_metadata->fullContactInfo->birthDate );

			$age = ( date( 'md', date( 'U', mktime( 0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0] ) ) ) > date( 'md' )
				? ( ( date( 'Y' ) - $birthDate[0] ) - 1 )
				: ( date( 'Y' ) - $birthDate[0] ) );
			return $age;
		}

		if ( isset( $this->profile->dateOfBirth ) ) {

			$birthDate = explode( '-', $this->profile->birthday );

			$age = ( date( 'md', date( 'U', mktime( 0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0] ) ) ) > date( 'md' )
				? ( ( date( 'Y' ) - $birthDate[0] ) - 1 )
				: ( date( 'Y' ) - $birthDate[0] ) );
			return $age;
		}
		if ( isset( $this->profile->birthday ) ) {

			$birthDate = explode( '/', $this->profile->birthday );

			$age = ( date( 'md', date( 'U', mktime( 0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2] ) ) ) > date( 'md' )
				? ( ( date( 'Y' ) - $birthDate[2] ) - 1 )
				: ( date( 'Y' ) - $birthDate[2] ) );
			return $age;
		}

		return null;
	}


}
